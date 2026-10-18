<?php

namespace App\Services;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class DoctorService
{
    public function getDoctorsCounts()
    {
        return Cache::remember(
                'doctors_counts',
                3600,
                fn () => Doctor::where('status', 'active')->count()
            );

    }
    public function getDoctorsCountsByClinic(){
       return Cache::remember(
                'doctors_counts_by_clinic',
                3600,
                fn () => Doctor::query()
                    ->where('status', 'active')
                    ->groupBy('clinic_id')
                    ->selectRaw('clinic_id, COUNT(*) as count')
                    ->get()
                    ->mapWithKeys(fn ($item) => [
                        $item->clinic_id => $item->count,
                    ])
                    ->all()
            );
    }
}
