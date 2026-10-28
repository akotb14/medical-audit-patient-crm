<?php

use App\Enums\RoleEnum;

/*
|--------------------------------------------------------------------------
| Regression cover for REVIEW-004 (no rate limiting on login) and
| REVIEW-005 (an unknown username was rejected by an `exists` rule, which let
| anyone enumerate accounts).
|
| The factories hash the literal password "password" for every user they build.
|--------------------------------------------------------------------------
*/

it('rejects an unknown username with the same message as a wrong password', function () {
    $user = createUserWithRole(RoleEnum::ADMIN);

    $unknownUsername = $this->from(route('auth.login'))->post(route('login'), [
        'username' => 'no-such-user',
        'password' => 'password',
    ]);

    $wrongPassword = $this->from(route('auth.login'))->post(route('login'), [
        'username' => $user->username,
        'password' => 'not-the-password',
    ]);

    $unknownUsername->assertSessionHasErrors([
        'username' => 'اسم المستخدم أو كلمة المرور غير صحيحة.',
    ]);
    $wrongPassword->assertSessionHasErrors([
        'username' => 'اسم المستخدم أو كلمة المرور غير صحيحة.',
    ]);

    $this->assertGuest();
});

it('signs an administrator in and redirects to the dashboard', function () {
    $admin = createUserWithRole(RoleEnum::ADMIN);

    $this->post(route('login'), [
        'username' => $admin->username,
        'password' => 'password',
    ])->assertRedirect(route('dashboard.index'));

    $this->assertAuthenticatedAs($admin);
});

it('signs a receptionist in and redirects to the clinics list', function () {
    $receptionist = createUserWithRole(RoleEnum::RECEPTIONIST);

    $this->post(route('login'), [
        'username' => $receptionist->username,
        'password' => 'password',
    ])->assertRedirect(route('clinics.index'));

    $this->assertAuthenticatedAs($receptionist);
});

/*
| Asserting the credential message on each of the five failures is also what
| proves the limiter starts empty: the per-address bucket is shared by all the
| tests in this file, so a counter carried over from another test would make the
| very first attempt answer with the throttle message instead.
*/
it('refuses the correct password only after five failed attempts', function () {
    $admin = createUserWithRole(RoleEnum::ADMIN);

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->from(route('auth.login'))
            ->post(route('login'), [
                'username' => $admin->username,
                'password' => 'not-the-password',
            ])
            ->assertSessionHasErrors([
                'username' => 'اسم المستخدم أو كلمة المرور غير صحيحة.',
            ]);
    }

    $blocked = $this->from(route('auth.login'))->post(route('login'), [
        'username' => $admin->username,
        'password' => 'password',
    ]);

    $blocked->assertSessionHasErrors('username');

    expect(session('errors')->first('username'))
        ->toContain('كثيرة جدًا')
        ->not->toContain('غير صحيحة');

    $this->assertGuest();
});
