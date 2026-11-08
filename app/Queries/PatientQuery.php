<?php

namespace App\Queries;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;

class PatientQuery
{
    public function getStatistics() {
          return Patient::query()
            ->selectRaw("
            COUNT(*) AS total,

            COUNT(CASE
                WHEN allergies IS NOT NULL
                 AND TRIM(allergies) != ''
                THEN 1
            END) AS allergies_count,

            COUNT(CASE
                WHEN chronic_diseases IS NOT NULL
                 AND TRIM(chronic_diseases) != ''
                THEN 1
            END) AS chronic_diseases_count,

            (
                SELECT blood_group
                FROM patients
                WHERE deleted_at IS NULL
                  AND blood_group IS NOT NULL
                  AND TRIM(blood_group) != ''
                GROUP BY blood_group
                ORDER BY COUNT(*) DESC, blood_group ASC
                LIMIT 1
            ) AS most_common_blood_group
        ");
    }

    public function getIndexQuery(Builder $query, array $filters): Builder
    {
        return $query
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

            ->orderByDesc('id');
    }
}
