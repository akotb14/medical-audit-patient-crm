<?php

namespace App\Http\Requests;

use App\Enums\RoleEnum;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            //
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($this->route('user')),
            ],
            'role_id' => ['required', 'exists:roles,id', $this->adminRoleChangeRule()],
        ];
    }

    /**
     * Granting or revoking the administrator role is reserved to administrators.
     * The dedicated authorization rules still decide who may update users at
     * all; this rule guards only the administrator role itself.
     */
    private function adminRoleChangeRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_numeric($value)) {
                return;
            }

            $role = Role::query()->whereKey((int) $value)->first();
            $user = $this->route('user');

            if ($role === null || ! $user instanceof User) {
                return;
            }

            $submittedIsAdmin = $role->name === RoleEnum::ADMIN->value;
            $currentIsAdmin = $user->hasRole(RoleEnum::ADMIN->value);

            if ($submittedIsAdmin === $currentIsAdmin) {
                return;
            }

            $actor = $this->user();

            if (! $actor instanceof User || ! $actor->hasRole(RoleEnum::ADMIN->value)) {
                $fail('يملك المديرون فقط صلاحية منح أو سحب دور المدير.');
            }
        };
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم كامل مطلوب.',
            'name.string' => 'الاسم يجب أن يكون نصًا.',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرفًا.',

            'username.required' => 'اسم المستخدم مطلوب.',
            'username.string' => 'اسم المستخدم يجب أن يكون نصًا.',
            'username.max' => 'اسم المستخدم يجب ألا يتجاوز 255 حرفًا.',
            'username.unique' => 'اسم المسنخدم مستخدم بالفعل.',

            'role_id.required' => 'يرجى اختيار الدور.',
            'role_id.exists' => 'الدور المحدد غير موجود.',
        ];
    }
}
