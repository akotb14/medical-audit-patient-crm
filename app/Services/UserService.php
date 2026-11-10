<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
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

    public function isAdmin(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN->value);
    }

    public function paginateIndex(): array
    {
        return [
            'users' => User::with('roles')->orderBy(
                Role::query()->select('roles.name')->join(
                    'model_has_roles',
                    'roles.id',
                    '=',
                    'model_has_roles.role_id'
                )->whereColumn('roles.id', 'model_has_roles.role_id')
                    ->whereColumn('model_has_roles.model_id', 'users.id')
                    ->where('model_has_roles.model_type', (new User)->getMorphClass())->limit(1)
            )->simplePaginate(10),
            'totalUsers' => User::count(),
            'receptionCountRole' => User::role(RoleEnum::RECEPTIONIST)->count(),
        ];
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'password' => $data['password'],
            ]);
            $role = Role::query()->findOrFail($data['role_id']);
            if ($role->name === RoleEnum::ADMIN->value && ! $this->isAdmin(Auth::user())) {
                throw ValidationException::withMessages([
                    'role_id' => 'لا يوجد لديك صلاحية لاضافة مدير.',
                ]);
            }
            $user->assignRole($role);

            return $user;
        });
    }

    public function update(User $user, array $data)
    {
        $role = Role::findOrFail($data['role_id']);

        DB::transaction(function () use ($user, $data, $role) {
            $this->lockAdministratorRole();
            $administrators = $this->lockAdministrators();
            $lockedUser = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($role->name !== RoleEnum::ADMIN->value && $this->isLastAdministrator($lockedUser, $administrators)) {
                throw ValidationException::withMessages([
                    'role_id' => 'لا يمكن إزالة دور المدير من آخر مدير في النظام.',
                ]);
            }

            $lockedUser->update($data);
            $lockedUser->syncRoles([$role]);
        });
    }

    public function delete(User $user): string
    {
        return DB::transaction(function () use ($user): string {
            $this->lockAdministratorRole();
            $administrators = $this->lockAdministrators();
            $lockedUser = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($this->isLastAdministrator($lockedUser, $administrators)) {
                return 'last_admin';
            }
            $lockedUser->delete();

            return 'deleted';
        });
    }

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
     * The serialisation point for every change to administrator membership.
     *
     * Two administrators can demote each other at once, so locking the target
     * row alone serialises nothing. The administrator role row is a stable
     * record that every such change has to take first: whoever holds it is the
     * only transaction that can read the administrator count and write a role,
     * so the next one reads the state the previous one committed.
     */
    private function lockAdministratorRole(): void
    {
        Role::query()
            ->where('name', RoleEnum::ADMIN->value)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * The current administrators, read as a locking join rather than through a
     * `whereHas` subquery.
     *
     * MySQL answers a membership subquery from the transaction's read view, so
     * a process that waits on the lock and then counts still sees the snapshot
     * it started with — the demotion it waited for is invisible, and both
     * transactions believe two administrators remain. A direct locking join
     * reads the latest committed rows, so the count reflects the transaction
     * that just held the lock.
     *
     * @return Collection<int, User>
     */
    private function lockAdministrators(): Collection
    {
        return User::query()
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', (new User)->getMorphClass())
            ->where('roles.name', RoleEnum::ADMIN->value)
            ->select('users.*')
            ->orderBy('users.id')
            ->lockForUpdate()
            ->get();
    }

    /**
     * True when the locked user is the only administrator the locked read found.
     *
     * The membership comes from the locked set rather than from `hasRole()`:
     * the relation is a consistent read too, and inside a transaction that
     * waited on the role lock it could still describe the state before the
     * transaction that held it.
     *
     * @param  Collection<int, User>  $administrators
     */
    private function isLastAdministrator(User $user, Collection $administrators): bool
    {
        return $administrators->count() <= 1
            && $administrators->contains(fn (User $administrator): bool => $administrator->is($user));
    }
}
