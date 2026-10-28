<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    //

    public function index()
    {
        Gate::authorize('viewAny', User::class);
        $users = User::cursorPaginate(10);
        $receptionCountRole =  User::role(RoleEnum::RECEPTIONIST)->count();


        return view('users.index', compact('users', 'receptionCountRole'));
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
        $user = User::create($userRequest);

        $role = Role::findOrFail($request->role_id);
        $user->assignRole($role);

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

        $user->update($request->validated());
        $role = Role::findOrFail($request->role_id);
        $user->syncRoles([$role]);

        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم بنجاح');
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);
        $user = User::findOrFail($user->id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم بنجاح');
    }
}
