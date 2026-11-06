<?php

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\Models\Role;



it('renders the user edit page for an administrator', function () {
    actingAsAdmin();
    $user = User::factory()->create();

    $this->get(route('users.edit', $user))
        ->assertOk()
        ->assertViewHas('user', fn(User $viewUser): bool => $viewUser->is($user))
        ->assertViewHas('roles');
});

it('updates a user and syncs the submitted role', function () {
    actingAsAdmin();
    $user = User::factory()->create();
    $role = Role::findByName(RoleEnum::RECEPTIONIST->value);

    $this->put(route('users.update', $user), [
        'name' => 'سامي محمود عبد الله',
        'username' => 'sami.mahmoud',
        'role_id' => $role->id,
    ])->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'سامي محمود عبد الله',
        'username' => 'sami.mahmoud',
    ]);

    expect($user->refresh()->hasRole(RoleEnum::RECEPTIONIST->value))->toBeTrue();
});

it('deletes a user', function () {
    actingAsAdmin();
    $user = User::factory()->create();

    $this->delete(route('users.destroy', $user))
        ->assertRedirect(route('users.index'));

    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

it('refuses to delete a user that authored records', function () {
    actingAsAdmin();
    $author = User::factory()->create();
    Patient::factory()->create(['created_by' => $author->id]);

    $this->delete(route('users.destroy', $author))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted('users', [
        'id' => $author->id,
    ]);
});

/*
| The other branch of the guard. `it('deletes a user')` covers a user nothing
| references; this one holds a role as well, so a guard that refused on role
| assignments instead of records would fail here.
*/
it('still deletes a user that holds a role but authored nothing', function () {
    actingAsAdmin();
    $user = createUserWithRole(RoleEnum::RECEPTIONIST);

    $this->delete(route('users.destroy', $user))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

/*
| `patients.deleted_by` names the user who archived the file. Its foreign key
| releases the reference instead of refusing it, so the guard has to hold it on
| its own: deleting the archivist used to succeed and left the archive with
| nobody attributed.
*/
it('soft deletes the user who archived a patient while preserving the audit reference', function () {
    actingAsAdmin();

    $archivist = User::factory()->create();
    $patient = Patient::factory()->create();

    $patient->forceFill([
        'deleted_by' => $archivist->id,
    ])->save();

    $patient->delete();

    $this->delete(route('users.destroy', $archivist))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    $this->assertSoftDeleted('users', [
        'id' => $archivist->id,
    ]);

    $this->assertSoftDeleted('patients', [
        'id' => $patient->id,
        'deleted_by' => $archivist->id,
    ]);
});

it('forbids a receptionist from opening the user edit page', function () {
    actingAsReceptionist();
    $user = User::factory()->create();

    $this->get(route('users.edit', $user))->assertForbidden();
});

it('redirects a guest from the user edit page to the login page', function () {
    $user = User::factory()->create();

    $this->get(route('users.edit', $user))->assertRedirect(route('login'));
});

/**
 * Leave the given user as the only holder of the administrator role.
 *
 * The seeder creates an administrator of its own, so a test about "the last
 * administrator" has to release the role everywhere else first.
 */
function makeOnlyAdmin(User $admin): void
{
    User::role(RoleEnum::ADMIN->value)
        ->whereKeyNot($admin->getKey())
        ->get()
        ->each(fn(User $other) => $other->removeRole(RoleEnum::ADMIN->value));
}

/*
| The administrator role is the only one that opens user management and the
| dashboard. Nothing stopped an administrator from deleting their own account
| or from demoting the only account holding the role, which left the system
| without an administrator.
*/
describe('protecting the last administrator', function () {
    it('refuses to let an administrator delete their own account', function () {
        $admin = actingAsAdmin();

        $this->delete(route('users.destroy', $admin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    });

    it('refuses to remove the administrator role from the last administrator', function () {
        $actor = actingAsReceptionist();
        $actor->givePermissionTo(PermissionEnum::USERS_UPDATE->value);
        $lastAdmin = createUserWithRole(RoleEnum::ADMIN);
        makeOnlyAdmin($lastAdmin);
        $receptionistRole = Role::findByName(RoleEnum::RECEPTIONIST->value);

        $this->from(route('users.edit', $lastAdmin))
            ->put(route('users.update', $lastAdmin), [
                'name' => $lastAdmin->name,
                'username' => $lastAdmin->username,
                'role_id' => $receptionistRole->id,
            ])
            ->assertSessionHasErrors('role_id');

        expect($lastAdmin->refresh()->hasRole(RoleEnum::ADMIN->value))->toBeTrue();
    });

    it('allows demoting an administrator while another administrator remains', function () {
        $admin = actingAsAdmin();
        createUserWithRole(RoleEnum::ADMIN);
        $receptionistRole = Role::findByName(RoleEnum::RECEPTIONIST->value);

        $this->put(route('users.update', $admin), [
            'name' => $admin->name,
            'username' => $admin->username,
            'role_id' => $receptionistRole->id,
        ])->assertRedirect(route('patients.index'));

        expect($admin->refresh()->hasRole(RoleEnum::RECEPTIONIST->value))->toBeTrue();
    });

    it('allows deleting an administrator while another administrator remains', function () {
        actingAsAdmin();
        $otherAdmin = createUserWithRole(RoleEnum::ADMIN);

        $this->delete(route('users.destroy', $otherAdmin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('users', ['id' => $otherAdmin->id]);
    });

    /*
    | The delete guard is not only about the signed in account: an actor that
    | holds the permission directly must not be able to remove the last
    | administrator either.
    */
    it('refuses to delete the last administrator for another account holding the permission', function () {
        $actor = actingAsReceptionist();
        $actor->givePermissionTo(PermissionEnum::USERS_DELETE->value);
        $lastAdmin = createUserWithRole(RoleEnum::ADMIN);
        makeOnlyAdmin($lastAdmin);

        $this->delete(route('users.destroy', $lastAdmin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted('users', ['id' => $lastAdmin->id]);
    });
});

/*
| The account and its role are written as one unit. Without the transaction a
| failure while the role was assigned left the account behind without one.
*/
describe('creating a user', function () {
    it('creates the account with the selected role', function () {
        actingAsAdmin();
        $role = Role::findByName(RoleEnum::RECEPTIONIST->value);

        $this->post(route('users.store'), [
            'name' => 'هدى سمير عبد الرحمن',
            'username' => 'huda.samir',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role_id' => $role->id,
        ])->assertRedirect(route('users.index'));

        $user = User::query()->where('username', 'huda.samir')->firstOrFail();

        expect($user->hasRole(RoleEnum::RECEPTIONIST->value))->toBeTrue();
    });


    it('creates the account with the selected admin role', function () {
        actingAsAdmin();
        $role = Role::findByName(RoleEnum::ADMIN->value);

        $this->post(route('users.store'), [
            'name' => 'هدى سمير1 عبد الرحمن',
            'username' => 'huda.samir1',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role_id' => $role->id,
        ])->assertRedirect(route('users.index'));

        $user = User::query()->where('username', 'huda.samir1')->firstOrFail();

        expect($user->hasRole(RoleEnum::ADMIN->value))->toBeTrue();
    });

    /*
    | The role row is removed between the validation and the role lookup, which is
    | the failure the transaction guards against: neither the account nor the role
    | deletion may survive it.
    */
    it('rolls the account back when the role cannot be assigned', function () {
        actingAsAdmin();
        $role = Role::findByName(RoleEnum::RECEPTIONIST->value);

        User::created(function () use ($role): void {
            Role::query()->whereKey($role->id)->delete();
        });
        try {
            expect(fn() => $this->withoutExceptionHandling()->post(route('users.store'), [
                'name' => 'هدى سمير عبد الرحمن',
                'username' => 'huda.samir',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'role_id' => $role->id,
            ]))->toThrow(ModelNotFoundException::class);
        } finally {
            User::flushEventListeners();
        }

        expect(User::query()->where('username', 'huda.samir')->exists())->toBeFalse()
            ->and(Role::query()->whereKey($role->id)->exists())->toBeTrue();
    });
});
