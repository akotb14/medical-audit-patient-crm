<?php

use App\Enums\RoleEnum;
use App\Models\User;





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
