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

    /**
     * Every clinic with the doctors it holds, for the clinics index.
     *
     * @return Collection<int, Clinic>
     */
    public function getClinicsWithDoctors(): Collection
    {
        return Clinic::with('doctors:id,clinic_id,fullname')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?int $actorId): Clinic
    {
        $clinic = Clinic::create([
            ...$data,
            'created_by' => $actorId,
        ]);

        $this->flushClinicOptions();

        return $clinic;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Clinic $clinic, array $data, ?int $actorId): void
    {
        $clinic->update([
            ...$data,
            'updated_by' => $actorId,
        ]);

        $this->flushClinicOptions();
    }

    /**
     * Delete a clinic that holds no doctors.
     *
     * Deleting the clinic would release the clinic reference on every doctor it
     * holds, and those doctors are what the historical visits hang from. A clinic
     * that still has a staff is deactivated instead, the way the `inactive`
     * status already allows. The row is locked before the check, so a doctor
     * cannot be attached between the check and the delete.
     *
     * @return bool whether the clinic was deleted
     */
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
