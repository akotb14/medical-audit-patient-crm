<?php

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Support\Facades\Cache;

class ClinicService
{
    public function getClinics()
    {

            return Cache::remember('clinics.all', 3600, function () {
                return Clinic::query()->where('status', 'active')->get(['id', 'name'])->toArray();
                });

    }
}
