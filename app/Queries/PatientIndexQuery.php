<?php
namespace App\Queries;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;

class PatientIndexQuery
{
    public function build(array $filters): Builder
    {
        return Patient::query()
            ->when($filters['search'] ?? null, function (
                Builder $query,
                string $search
            ) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('mrn', 'like', "%{$search}%")
                    ->orwhere('fullname', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['blood_group'] ?? null, function (
                Builder $query,
                int|string $blood_group
            ) {
                $query->where('blood_group', $blood_group);
            })

            ->latest();
    }
}

