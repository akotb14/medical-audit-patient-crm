<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class VisitSeeder extends Seeder
{
    /**
     * @param  int  $completedCount  Visits that already took place.
     * @param  int  $upcomingCount  Visits still waiting to happen.
     * @param  int  $cancelledCount  Visits that were cancelled.
     */
    public function __construct(
        public int $completedCount = 120,
        public int $upcomingCount = 50,
        public int $cancelledCount = 30,
    ) {}

    /**
     * Run the database seeds.
     *
     * Visits only reference active doctors, matching the doctors offered by the
     * booking form, and patients/doctors/staff are recycled instead of recreated.
     */
    public function run(): void
    {
        $users = User::query()->get();
        $patients = Patient::query()->get(['id']);
        $doctors = Doctor::query()->where('status', 'active')->get(['id']);

        Visit::factory()
            ->count($this->completedCount)
            ->completed()
            ->recycle($patients)
            ->recycle($doctors)
            ->recycle($users)
            ->create();

        Visit::factory()
            ->count($this->upcomingCount)
            ->recycle($patients)
            ->recycle($doctors)
            ->recycle($users)
            ->create();

        Visit::factory()
            ->count($this->cancelledCount)
            ->cancelled()
            ->recycle($patients)
            ->recycle($doctors)
            ->recycle($users)
            ->create();
    }
}
