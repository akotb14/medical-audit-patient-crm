<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'اسم المستخدم مطلوب',
            'password.required' => 'كلمة المرور مطلوبة',
        ];
    }

    public function throttleKey(): string
    {
        return 'login:user:' . Str::lower(
            trim($this->input('username', ''))
        ) . '|' . $this->ip();
    }

    public function ipThrottleKey(): string
    {
        return 'login:ip:' . $this->ip();
    }

    public function ensureIsNotRateLimited(): void
    {
        $userKey = $this->throttleKey();
        $ipKey = $this->ipThrottleKey();

        $userLimited = RateLimiter::tooManyAttempts($userKey, 5);
        $ipLimited = RateLimiter::tooManyAttempts($ipKey, 20);

        if (! $userLimited && ! $ipLimited) {
            return;
        }

        $seconds = max(
            $userLimited ? RateLimiter::availableIn($userKey) : 0,
            $ipLimited ? RateLimiter::availableIn($ipKey) : 0
        );

        throw ValidationException::withMessages([
            'username' => "محاولات تسجيل الدخول كثيرة جدًا. حاول مرة أخرى بعد {$seconds} ثانية.",
        ]);
    }
}
