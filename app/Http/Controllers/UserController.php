<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Clinic;
use App\Models\Complaint;
use App\Models\Doctor;
use App\Models\FollowUp;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
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

    //

    public function index()
    {
        Gate::authorize('viewAny', User::class);
        $users = User::with(['roles.permissions', 'permissions'])->cursorPaginate(10);
        $totalUsers = User::count();
        $receptionCountRole = User::role(RoleEnum::RECEPTIONIST)->count();

        return view('users.index', compact('users', 'totalUsers', 'receptionCountRole'));
    }

    public function create()
    {
        Gate::authorize('create', User::class);
        $roles = Role::all();

        return view('users.create', compact('roles'));
    }

    public function store(CreateUserRequest $request)
    {
        Gate::authorize('create', User::class);
        $userRequest = $request->validated();
        $existUser = User::where('username', $request->username)->first();
        if ($existUser) {
            return back()->withErrors(['username' => 'الاسم المستخدم موجود بالفعل']);
        }
        DB::transaction(function () use ($request, $userRequest) {
            $user = User::create($userRequest);

            $role = Role::findOrFail($request->role_id);
            $user->assignRole($role);
        });

        return redirect()->route('users.index')->with('success', 'تم انشاء المستخدم بنجاح');
    }

    public function edit(User $user)
    {
        Gate::authorize('update', $user);
        $user = User::findOrFail($user->id);
        $roles = Role::all();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        Gate::authorize('update', $user);
        $user = User::findOrFail($user->id);
        $role = Role::findOrFail($request->role_id);

        /*
        | The administrator role is the only one that opens user management and
        | the dashboard, so it is kept on the account that still holds it instead
        | of leaving the system without an administrator. The administrator rows
        | are locked before they are counted, so concurrent demotions cannot both
        | read the same count and leave the system without one.
        */
        DB::transaction(function () use ($request, $user, $role): void {
            $administratorCount = $this->lockAdministrators()->count();
            $lockedUser = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($this->isRemovingLastAdmin($lockedUser, $role, $administratorCount)) {
                throw ValidationException::withMessages([
                    'role_id' => 'لا يمكن إزالة دور المدير من آخر مدير في النظام.',
                ]);
            }

            $lockedUser->update($request->validated());
            $lockedUser->syncRoles([$role]);
        });

        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم بنجاح');
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);
        $user = User::findOrFail($user->id);

        // Deleting the signed in account ends the session it is using.
        if ($user->is(Auth::user())) {
            return redirect()->route('users.index')
                ->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }

        /*
        | The administrator rows are locked and counted inside the transaction,
        | so concurrent deletions and demotions serialize even when they target
        | different accounts. Every record that names this user as the one who
        | created, updated, assigned or archived it keeps a live reference to
        | them, so deleting the row is refused instead of failing on the foreign
        | key or erasing the audit trail.
        */
        $outcome = DB::transaction(function () use ($user): string {
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

        return match ($outcome) {
            'last_admin' => redirect()->route('users.index')
                ->with('error', 'لا يمكن حذف آخر مدير في النظام.'),
            'referenced' => redirect()->route('users.index')
                ->with('error', 'لا يمكن حذف المستخدم لارتباطه بسجلات موجودة في النظام.'),
            default => redirect()->route('users.index')
                ->with('success', 'تم حذف المستخدم بنجاح'),
        };
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
