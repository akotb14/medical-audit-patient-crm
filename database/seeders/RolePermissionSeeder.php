<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Clear cached permissions before changing them.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        // 1. Create all permissions.
        foreach (PermissionEnum::cases() as $permission) {
            Permission::findOrCreate(
                $permission->value,
                $guard
            );
        }

        // The permission collection was cached while creating the permissions above,
        // so it has to be flushed again before the roles resolve them by name.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 2. Create roles.
        $admin = Role::findOrCreate(
            RoleEnum::ADMIN->value,
            $guard
        );

        $receptionist = Role::findOrCreate(
            RoleEnum::RECEPTIONIST->value,
            $guard
        );
        $admin->syncPermissions(
            PermissionEnum::values()
        );
        $receptionistPermissions = array_values(
            array_diff(
                PermissionEnum::values(),
                [
                    PermissionEnum::USERS_CREATE->value,
                    PermissionEnum::USERS_VIEW->value,
                    PermissionEnum::USERS_UPDATE->value,
                    PermissionEnum::USERS_DELETE->value,

                    PermissionEnum::CLINICS_CREATE->value,
                    PermissionEnum::CLINICS_UPDATE->value,
                    PermissionEnum::CLINICS_DELETE->value,

                    PermissionEnum::PATIENTS_DELETE->value,

                    PermissionEnum::DOCTORS_CREATE->value,
                    PermissionEnum::DOCTORS_UPDATE->value,
                    PermissionEnum::DOCTORS_DELETE->value,

                    PermissionEnum::VISITS_UPDATE->value,
                    PermissionEnum::VISITS_DELETE->value,

                    PermissionEnum::FOLLOWUPS_UPDATE->value,
                    PermissionEnum::FOLLOWUPS_DELETE->value,

                    PermissionEnum::SURVEYS_UPDATE->value,
                    PermissionEnum::SURVEYS_DELETE->value,

                    PermissionEnum::COMPLAINTS_UPDATE->value,
                    PermissionEnum::COMPLAINTS_ASSIGN->value,
                    PermissionEnum::COMPLAINTS_ADVANCE->value,
                    PermissionEnum::COMPLAINTS_REOPEN->value,
                    PermissionEnum::COMPLAINTS_DELETE->value,
                ]
            )
        );
        $receptionist->syncPermissions($receptionistPermissions);

        // Create Admin user
        $user = User::firstOrCreate(
            ['username' => 'admin@clinic'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@123456'),
            ]
        );
        if (! $user->hasRole(RoleEnum::ADMIN->value)) {
            // Assign Admin role
            $user->syncRoles([
                RoleEnum::ADMIN->value,
            ]);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
