<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * @param  int  $count  Number of patient records to create.
     */
    public function __construct(public int $count = 100) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Patient::factory()
            ->count($this->count)
            ->recycle(User::query()->get())
            ->create();
    }
}
