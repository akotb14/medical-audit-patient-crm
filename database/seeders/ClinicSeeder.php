<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\User;
use Database\Factories\ClinicFactory;
use Illuminate\Database\Seeder;

class ClinicSeeder extends Seeder
{
    /**
     * @param  int  $count  Number of clinics to create (capped by the clinic catalog size).
     */
    public function __construct(public int $count = 10) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Clinic::factory()
            ->count(min($this->count, count(ClinicFactory::CATALOG)))
            ->recycle(User::query()->get())
            ->create();
    }
}
