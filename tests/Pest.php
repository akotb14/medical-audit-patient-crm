<?php

use App\Enums\RoleEnum;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Seed the roles and the permission catalogue.
 *
 * The container is rebuilt for every test, so this guard lives for a single test
 * and only stops the seeder from running twice inside it.
 */
function seedRolesAndPermissions(): void
{
    if (app()->has('tests.roles_seeded')) {
        return;
    }

    test()->seed(RolePermissionSeeder::class);

    app()->instance('tests.roles_seeded', true);
}

/**
 * Create a user holding the given role without signing them in.
 */
function createUserWithRole(RoleEnum $role): User
{
    seedRolesAndPermissions();

    $user = User::factory()->create();
    $user->assignRole($role->value);

    return $user;
}

/**
 * Create a user holding the given role and sign them in for the next request.
 */
function actingAsUserWithRole(RoleEnum $role): User
{
    $user = createUserWithRole($role);

    test()->actingAs($user);
    return $user;
}

/**
 * Create an administrator and sign them in for the next request.
 */
function actingAsAdmin(): User
{
    return actingAsUserWithRole(RoleEnum::ADMIN);
}

/**
 * Create a receptionist and sign them in for the next request.
 */
function actingAsReceptionist(): User
{
    return actingAsUserWithRole(RoleEnum::RECEPTIONIST);
}
