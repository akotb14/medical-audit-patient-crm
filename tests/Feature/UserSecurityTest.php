<?php

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Spatie\Permission\Models\Role;



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
