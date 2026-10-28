<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * The order matters: roles and permissions come first, then the staff accounts
     * every other record points at as creator/updater, and finally the clinical
     * data in foreign key order (clinics -> doctors -> patients -> visits ->
     * follow-ups -> surveys -> complaints).
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            ClinicSeeder::class,
            DoctorSeeder::class,
            PatientSeeder::class,
            VisitSeeder::class,
            FollowUpSeeder::class,
            SurveySeeder::class,
            ComplaintSeeder::class,
        ]);
    }
}
