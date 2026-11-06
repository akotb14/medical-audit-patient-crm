<?php

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClinicService
{
    public function getClinics(): Collection
    {
        $clinics = Cache::remember(
            'clinics.all',
            3600,
            fn () => Clinic::query()
                ->select('id', 'name')
                ->where('status', 'active')
                ->orderBy('name')
                ->get()
                ->toArray()
        );

        return Clinic::hydrate($clinics);
    }


    public function getClinicsWithDoctors(): Collection
    {
        return Clinic::with('doctors:id,clinic_id,fullname')->get();
    }


    public function create(array $data, ?int $actorId): Clinic
    {
        $clinic = Clinic::create([
            ...$data,
            'created_by' => $actorId,
        ]);

        $this->flushClinicOptions();

        return $clinic;
    }


    public function update(Clinic $clinic, array $data, ?int $actorId): void
    {
        $clinic->update([
            ...$data,
            'updated_by' => $actorId,
        ]);

        $this->flushClinicOptions();
    }

   
    public function delete(Clinic $clinic): bool
    {
        $hasDoctors = DB::transaction(function () use ($clinic): bool {
            $lockedClinic = Clinic::query()
                ->whereKey($clinic->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedClinic->doctors()->exists()) {
                return true;
            }

            $lockedClinic->delete();

            return false;
        });

        if ($hasDoctors) {
            return false;
        }

        $this->flushClinicOptions();

        return true;
    }

    private function flushClinicOptions(): void
    {
        Cache::forget('clinics.all');
    }
}
