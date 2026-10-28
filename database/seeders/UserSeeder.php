<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Number of clinic staff accounts to generate.
     */
    public const STAFF_COUNT = 6;

    /**
     * Run the database seeds.
     *
     * Staff accounts only ever receive the receptionist role. The administrator
     * account created by {@see RolePermissionSeeder} is left untouched.
     */
    public function run(): void
    {
        $role = RoleEnum::RECEPTIONIST->value;

        $users = User::factory()->count(self::STAFF_COUNT);

        if (Role::query()->where('name', $role)->exists()) {
            $users = $users->receptionist();
        }

        $users->create();
    }
}
