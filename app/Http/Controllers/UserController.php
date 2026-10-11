<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    //

    public function index()
    {
        $users = User::cursorPaginate(10);
        $receptionCountRole = User::with('roles')->get()->filter(
            fn($user) => $user->roles->where('name', RoleEnum::Receptionist)->toArray()
        )->count();

        return view('users.index', compact('users', 'receptionCountRole'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(CreateUserRequest $request)
    {
        $userRequest = $request->validated();
        $existUser = User::where('username', $request->username)->first();
        if ($existUser) return back()->withErrors(['username' => 'الاسم المستخدم موجود بالفعل']);
        $user = User::create($userRequest);

        $role = Role::findOrFail($request->role_id);
        $user->assignRole($role);

        return redirect()->route('users.index')->with('success', 'تم انشاء المستخدم بنجاح');
    }
    public function edit(int $userId)
    {
        $user = User::findOrFail($userId);
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }
    public function update(UpdateUserRequest $request, User $user)
    {
        $user = User::findOrFail($user->id);
        $user->update($request->validated());
        $role = Role::findOrFail($request->role_id);
        $user->syncRoles([$role]);
        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم بنجاح');
    }
    public function destroy(int $userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();
        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم بنجاح');
    }
}