<?php

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Regression cover for AUD-17 and AUD-18.
|
| AUD-17 — the password accepted for a new account only had to be six
| characters long and was submitted without a confirmation field.
| AUD-18 — any account holding `users.update` could grant itself or someone
| else the administrator role, which opens user management and the dashboard.
|--------------------------------------------------------------------------
*/

function userStorePayload(Role $role, array $overrides = []): array
{
    return [
        'name' => 'حساب اختبار',
        'username' => 'policy.test',
        'password' => 'strong-pass',
        'password_confirmation' => 'strong-pass',
        'role_id' => $role->id,
        ...$overrides,
    ];
}

describe('the password policy of a new account', function () {
    it('refuses a password shorter than eight characters', function () {
        actingAsAdmin();
        $role = Role::findByName(RoleEnum::RECEPTIONIST->value);

        $this->from(route('users.create'))
            ->post(route('users.store'), userStorePayload($role, [
                'password' => 'short',
                'password_confirmation' => 'short',
            ]))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['username' => 'policy.test']);
    });

    it('refuses a password whose confirmation does not match', function () {
        actingAsAdmin();
        $role = Role::findByName(RoleEnum::RECEPTIONIST->value);

        $this->from(route('users.create'))
            ->post(route('users.store'), userStorePayload($role, [
                'password_confirmation' => 'different-pass',
            ]))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['username' => 'policy.test']);
    });

    it('accepts a confirmed password of eight characters', function () {
        actingAsAdmin();
        $role = Role::findByName(RoleEnum::RECEPTIONIST->value);

        $this->post(route('users.store'), userStorePayload($role, [
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]))->assertRedirect(route('users.index'))
            ->assertSessionHasNoErrors();

        expect(User::query()->where('username', 'policy.test')->exists())->toBeTrue();
    });
});

describe('granting or revoking the administrator role', function () {
    it('refuses an account holding users.update from promoting itself', function () {
        $actor = actingAsReceptionist();
        $actor->givePermissionTo(PermissionEnum::USERS_UPDATE->value);
        $adminRole = Role::findByName(RoleEnum::ADMIN->value);

        $this->put(route('users.update', $actor), [
            'name' => $actor->name,
            'username' => $actor->username,
            'role_id' => $adminRole->id,
        ])->assertSessionHasErrors('role_id');

        expect($actor->refresh()->hasRole(RoleEnum::ADMIN->value))->toBeFalse();
    });

    it('refuses an account holding users.update from demoting an administrator', function () {
        $actor = actingAsReceptionist();
        $actor->givePermissionTo(PermissionEnum::USERS_UPDATE->value);
        $admin = createUserWithRole(RoleEnum::ADMIN);
        createUserWithRole(RoleEnum::ADMIN);

        $this->put(route('users.update', $admin), [
            'name' => $admin->name,
            'username' => $admin->username,
            'role_id' => Role::findByName(RoleEnum::RECEPTIONIST->value)->id,
        ])->assertSessionHasErrors('role_id');

        expect($admin->refresh()->hasRole(RoleEnum::ADMIN->value))->toBeTrue();
    });

    it('refuses an account holding users.create from creating an administrator', function () {
        $actor = actingAsReceptionist();
        $actor->givePermissionTo(PermissionEnum::USERS_CREATE->value);

        $this->post(route('users.store'), userStorePayload(
            Role::findByName(RoleEnum::ADMIN->value)
        ))->assertSessionHasErrors('role_id');

        $this->assertDatabaseMissing('users', ['username' => 'policy.test']);
    });

    it('lets an administrator promote another account', function () {
        actingAsAdmin();
        $user = User::factory()->create();

        $this->put(route('users.update', $user), [
            'name' => $user->name,
            'username' => $user->username,
            'role_id' => Role::findByName(RoleEnum::ADMIN->value)->id,
        ])->assertRedirect(route('users.index'))
            ->assertSessionHasNoErrors();

        expect($user->refresh()->hasRole(RoleEnum::ADMIN->value))->toBeTrue();
    });

    it('still lets an administrator update an account without changing its role', function () {
        actingAsAdmin();
        $user = createUserWithRole(RoleEnum::RECEPTIONIST);

        $this->put(route('users.update', $user), [
            'name' => 'اسم محدث',
            'username' => $user->username,
            'role_id' => Role::findByName(RoleEnum::RECEPTIONIST->value)->id,
        ])->assertRedirect(route('users.index'))
            ->assertSessionHasNoErrors();

        expect($user->refresh()->name)->toBe('اسم محدث');
    });
});
