<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index()
    {
        Gate::authorize('viewAny', User::class);

        return view('users.index', $this->userService->paginateIndex());
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

        $user = $this->userService->create($request->validated(), (int) $request->role_id);

        if ($user === null) {
            return back()->withErrors(['username' => 'الاسم المستخدم موجود بالفعل']);
        }

        return redirect()->route('users.index')->with('success', 'تم انشاء المستخدم بنجاح');
    }

    public function edit(User $user)
    {
        Gate::authorize('update', $user);
        $roles = Role::all();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        Gate::authorize('update', $user);

        $this->userService->update($user, $request->validated(), (int) $request->role_id);

        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم بنجاح');
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        // Deleting the signed in account ends the session it is using.
        if ($user->is(Auth::user())) {
            return redirect()->route('users.index')
                ->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }

        return match ($this->userService->delete($user)) {
            'last_admin' => redirect()->route('users.index')
                ->with('error', 'لا يمكن حذف آخر مدير في النظام.'),
            'referenced' => redirect()->route('users.index')
                ->with('error', 'لا يمكن حذف المستخدم لارتباطه بسجلات موجودة في النظام.'),
            default => redirect()->route('users.index')
                ->with('success', 'تم حذف المستخدم بنجاح'),
        };
    }
}
