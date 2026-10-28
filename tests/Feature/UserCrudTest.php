<?php

use App\Enums\RoleEnum;
use App\Models\User;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Regression cover for REVIEW-001.
|
| Every user management action called Gate::authorize() without the model, so
| the policy resolved to a different ability and the edit form returned a 500
| instead of rendering.
|--------------------------------------------------------------------------
*/

it('renders the user edit page for an administrator', function () {
    actingAsAdmin();
    $user = User::factory()->create();

    $this->get(route('users.edit', $user))
        ->assertOk()
        ->assertViewHas('user', fn (User $viewUser): bool => $viewUser->is($user))
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

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
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
