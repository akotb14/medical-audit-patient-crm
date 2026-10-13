<?php

namespace App\Providers;

use App\Models\Doctor;
use App\Services\ClinicService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
  /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        View::share('clinics', app(ClinicService::class)->getClinics());
        
        View::share(
            'doctors_counts',
            Cache::remember('doctors_counts', 3600, function () {
                return Doctor::where('status', 'active')->count();
            })
        );

        View::share(
            'doctors_counts_by_clinic',
            Cache::remember('doctors_counts_by_clinic', 3600, function () {
               $doctor =  Doctor::where('status', 'active')->groupBy('clinic_id')->selectRaw('clinic_id, count(*) as count')->get();
                // change format of array to ['clinic_id' => count]
                return $doctor->mapWithKeys(function ($item) {
                    return [$item->clinic_id => $item->count];
                })->toArray();
            })
        );
    }
}
