<?php

use App\Enums\RoleEnum;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Regression cover for AUD-12 and AUD-13.
|
| The users register rendered fields the `users` table does not have, so every
| account showed the same fabricated values: eight permissions, an active
| status and "منذ 15 دقيقة" as the last login. The role tabs, the search box
| and the roles-matrix button had no handler behind them. The register now
| shows the stored username and the real role permissions, and the inert
| controls are gone.
|--------------------------------------------------------------------------
*/

it('shows the stored account data and the real permission count', function () {
    actingAsAdmin();
    $user = createUserWithRole(RoleEnum::RECEPTIONIST);

    $this->get(route('users.index'))
        ->assertOk()
        ->assertViewHas('totalUsers', User::count())
        ->assertSee($user->name)
        ->assertSee($user->username)
        ->assertSee($user->getAllPermissions()->count().' صلاحيات')
        ->assertSee(RoleEnum::RECEPTIONIST->value);
});

it('does not present values the account does not store', function () {
    actingAsAdmin();

    $this->get(route('users.index'))
        ->assertOk()
        ->assertDontSee('منذ 15 دقيقة')
        ->assertDontSee('roles_matrix')
        ->assertDontSee('userSearchInput')
        ->assertDontSee('user-role-tab');
});

it('names the accounts that hold no role', function () {
    actingAsAdmin();
    User::factory()->create();

    $this->get(route('users.index'))
        ->assertOk()
        ->assertSee('بدون دور');
});
