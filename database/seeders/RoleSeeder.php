<?php

namespace Database\Seeders;

use App\RoleEnum;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Admin = Role::firstOrCreate(['name' => RoleEnum::ADMIN, 'guard_name' => 'web']);
        $Receptionist = Role::firstOrCreate(['name' => RoleEnum::Receptionist, 'guard_name' => 'web']);
    }
}
