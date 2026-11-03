<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Models\Clinic;
use App\Models\Complaint;
use App\Models\Doctor;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * The number of users a dropdown loads before the staff list grows past it.
     * The currently selected user is always kept in the list.
     */
    private const OPTION_LIMIT = 500;

    /**
     * The columns an assignee dropdown renders.
     *
     * @var list<string>
     */
    private const OPTION_COLUMNS = ['id', 'name'];

    /**
     * The audit columns that hold a user id and keep referring to that user,
     * keyed by the model that owns them.
     *
     * `patients.deleted_by` is released by its own foreign key, but it still
     * attributes an archive to the user who performed it, so the guard treats it
     * as a live reference rather than letting the deletion erase the audit trail.
     *
     * @var array<class-string<Model>, list<string>>
     */
    private const USER_REFERENCES = [
        Clinic::class => ['created_by', 'updated_by'],
        Complaint::class => ['created_by', 'updated_by', 'assigned_to'],
        Doctor::class => ['created_by', 'updated_by'],
        FollowUp::class => ['created_by', 'updated_by'],
        Patient::class => ['created_by', 'updated_by', 'deleted_by'],
        Survey::class => ['created_by', 'updated_by'],
        Visit::class => ['created_by', 'updated_by'],
    ];

    /**
     * The staff list, with the totals its header describes.
     *
     * @return array{users: CursorPaginator<int, User>, totalUsers: int, receptionCountRole: int}
     */
    public function paginateIndex(): array
    {
        return [
            'users' => User::with(['roles.permissions', 'permissions'])->cursorPaginate(10),
            'totalUsers' => User::count(),
            'receptionCountRole' => User::role(RoleEnum::RECEPTIONIST)->count(),
        ];
    }

    /**
     * Create a staff account under the submitted role.
     *
     * @param  array<string, mixed>  $data
     * @return User|null null when the username is already taken
     */
    public function create(array $data, int $roleId): ?User
    {
        if (User::where('username', $data['username'] ?? null)->exists()) {
            return null;
        }

        return DB::transaction(function () use ($data, $roleId): User {
            $user = User::create($data);

            $user->assignRole(Role::findOrFail($roleId));

            return $user;
        });
    }

    /**
     * Update a staff account, keeping the administrator role on its last holder.
     *
     * The administrator role is the only one that opens user management and the
     * dashboard, so it is kept on the account that still holds it instead of
     * leaving the system without an administrator. The administrator rows are
     * locked before they are counted, so concurrent demotions cannot both read
     * the same count and leave the system without one.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data, int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        DB::transaction(function () use ($user, $data, $role): void {
            $administratorCount = $this->lockAdministrators()->count();
            $lockedUser = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($this->isRemovingLastAdmin($lockedUser, $role, $administratorCount)) {
                throw ValidationException::withMessages([
                    'role_id' => 'لا يمكن إزالة دور المدير من آخر مدير في النظام.',
                ]);
            }

            $lockedUser->update($data);
            $lockedUser->syncRoles([$role]);
        });
    }

    /**
     * Delete a staff account that no record refers to.
     *
     * The administrator rows are locked and counted inside the transaction, so
     * concurrent deletions and demotions serialize even when they target
     * different accounts. Every record that names this user as the one who
     * created, updated, assigned or archived it keeps a live reference to them,
     * so deleting the row is refused instead of failing on the foreign key or
     * erasing the audit trail.
     *
     * @return string one of `deleted`, `last_admin`, `referenced`
     */
    public function delete(User $user): string
    {
        return DB::transaction(function () use ($user): string {
            $administratorCount = $this->lockAdministrators()->count();
            $lockedUser = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedUser->hasRole(RoleEnum::ADMIN->value) && $administratorCount <= 1) {
                return 'last_admin';
            }

            if ($this->isReferencedByRecords($lockedUser)) {
                return 'referenced';
            }

            $lockedUser->delete();

            return 'deleted';
        });
    }

    /**
     * The staff options offered by an assignee dropdown, bounded and ordered by
     * name.
     *
     * The user a complaint is already assigned to stays in the list even when the
     * staff list outgrew the limit: dropping it would leave the field pointing at
     * an option that is not there, and the browser would submit whichever name
     * happened to be first instead.
     *
     * @return Collection<int, User>
     */
    public function getAssigneeOptions(?int $selectedUserId = null): Collection
    {
        $users = $this->optionQuery()
            ->limit(self::OPTION_LIMIT)
            ->get();

        if ($selectedUserId === null || $users->contains('id', $selectedUserId)) {
            return $users;
        }

        $selectedUser = $this->optionQuery()->find($selectedUserId);

        if ($selectedUser === null) {
            return $users;
        }

        return $users
            ->push($selectedUser)
            ->sortBy('name')
            ->values();
    }

    /**
     * @return Builder<User>
     */
    private function optionQuery(): Builder
    {
        return User::query()
            ->select(self::OPTION_COLUMNS)
            ->with('roles:id,name')
            ->orderBy('name');
    }

    /**
     * Lock every row holding the administrator role and return the locked set.
     *
     * Every path that can demote or delete an administrator takes this lock
     * first, so concurrent requests serialize even when they target different
     * accounts, and counting the locked set is a consistent read.
     *
     * The role membership lives in `model_has_roles`, so the membership table is
     * joined into the locking statement instead of being read through the role
     * scope's subquery. A locking read that only locked `users` evaluated the
     * membership subquery against the transaction snapshot, so a demotion that
     * had already committed its membership change could still be counted while
     * its lock was being acquired, and two concurrent demotions could both pass
     * the last-administrator check.
     *
     * @return Collection<int, User>
     */
    private function lockAdministrators(): Collection
    {
        return User::query()
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', (new User)->getMorphClass())
            ->where('roles.name', RoleEnum::ADMIN->value)
            ->select('users.*')
            ->orderBy('users.id')
            ->lockForUpdate()
            ->get();
    }

    /**
     * Whether the submitted role would take the administrator role away from the
     * only account holding it.
     */
    private function isRemovingLastAdmin(User $user, Role $role, int $administratorCount): bool
    {
        return $role->name !== RoleEnum::ADMIN->value
            && $user->hasRole(RoleEnum::ADMIN->value)
            && $administratorCount <= 1;
    }

    /**
     * Whether any record still references this user through an audit column.
     *
     * The query builder is used on purpose: the soft delete scope hides archived
     * rows, and the foreign key still refuses to release them.
     */
    private function isReferencedByRecords(User $user): bool
    {
        foreach (self::USER_REFERENCES as $model => $columns) {
            $table = (new $model)->getTable();

            foreach ($columns as $column) {
                if (DB::table($table)->where($column, $user->id)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }
}
