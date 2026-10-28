<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'username' => 'required|exists:users,username',
            'password' => 'required|min:6',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'الاسم المستخدم مطلوب',
            'username.exists' => 'اسم المستخدم غير موجود',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور لا تقل عن 6 احرف',
        ];
    }
}
