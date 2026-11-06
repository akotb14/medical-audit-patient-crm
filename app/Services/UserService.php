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
            if ($role->name === RoleEnum::ADMIN->value && !$this->isAdmin(Auth::user())) {
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


    public function delete(User $user): string
    {
        return DB::transaction(function () use ($user): string {
            $administratorCount = $this->lockAdministrators()->count();
            $lockedUser = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedUser->hasRole(RoleEnum::ADMIN->value) && $administratorCount <= 1) {
                return 'last_admin';
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


    private function lockAdministrators(): Collection
    {

        return User::query()->whereHas('roles', fn($builder) => $builder->where('name', RoleEnum::ADMIN->value))->select('users.*')
            ->orderBy('users.id')
            ->lockForUpdate()->get();
    }


    private function isRemovingLastAdmin(User $user, Role $role, int $administratorCount): bool
    {
        return $role->name !== RoleEnum::ADMIN->value
            && $user->hasRole(RoleEnum::ADMIN->value)
            && $administratorCount <= 1;
    }

}
