<?php

namespace App\Http\Requests;

use App\Enums\RoleEnum;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => ['required', 'exists:roles,id', $this->adminGrantRule()],
        ];
    }

    /**
     * Only an administrator may create another administrator. The dedicated
     * authorization rules still decide who may manage users at all; this rule
     * guards only the role being granted.
     */
    private function adminGrantRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_numeric($value)) {
                return;
            }

            $role = Role::query()->whereKey((int) $value)->first();

            if ($role?->name !== RoleEnum::ADMIN->value) {
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

            'password.required' => 'كلمة المرور مطلوبة.',
            'password.string' => 'كلمة المرور يجب أن تكون نصًا.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور لا يطابق كلمة المرور.',

            'role_id.required' => 'يرجى اختيار الدور.',
            'role_id.exists' => 'الدور المحدد غير موجود.',
        ];
    }
}
