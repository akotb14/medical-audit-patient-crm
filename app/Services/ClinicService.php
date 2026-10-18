<?php

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

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
}
