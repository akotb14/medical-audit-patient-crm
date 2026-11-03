<?php

use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| Regression cover for AUD-02.
|
| The application runs with `APP_LOCALE=ar`, but `lang/` was empty, so the
| translator returned the rule key itself and the alert component printed
| `validation.*` to the user on every rule without a request-specific message.
|--------------------------------------------------------------------------
*/

it('translates a framework rule instead of returning its key', function () {
    $validator = Validator::make(['email' => 'not-an-email'], ['email' => 'email']);

    $message = (string) $validator->errors()->first('email');

    expect($message)->not->toBe('')
        ->and(str_starts_with($message, 'validation.'))->toBeFalse()
        ->and($message)->toMatch('/\p{Arabic}/u');
});

it('uses the translated attribute name in the message', function () {
    $validator = Validator::make(['email' => 'not-an-email'], ['email' => 'email']);

    expect((string) $validator->errors()->first('email'))->toContain('البريد الإلكتروني');
});

it('translates the authentication failure message', function () {
    expect(__('auth.failed'))->toBe('بيانات الدخول هذه غير مطابقة لسجلاتنا.');
});
