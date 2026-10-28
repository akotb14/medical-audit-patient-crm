<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Database\Factories\DoctorFactory;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * @param  int  $count  Total number of doctors to create.
     */
    public function __construct(public int $count = 30) {}

    /**
     * Run the database seeds.
     *
     * Doctors are spread evenly across the existing clinics and always receive a
     * specialty that belongs to the clinic they are attached to.
     */
    public function run(): void
    {
        $users = User::query()->get();
        $clinics = Clinic::query()->get();

        if ($clinics->isEmpty()) {
            $this->command?->warn('No clinics found; creating doctors without a clinic instead.');

            Doctor::factory()
                ->count($this->count)
                ->withoutClinic()
                ->recycle($users)
                ->create();

            return;
        }

        $perClinic = (int) ceil($this->count / $clinics->count());

        foreach ($clinics as $clinic) {
            Doctor::factory()
                ->count($perClinic)
                ->recycle($users)
                ->state(fn (): array => [
                    'clinic_id' => $clinic->getKey(),
                    'specialty' => fn (): string => fake('ar_EG')
                        ->randomElement(DoctorFactory::specialtiesFor($clinic->name)),
                ])
                ->create();
        }
    }
}
