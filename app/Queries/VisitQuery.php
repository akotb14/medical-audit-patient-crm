<?php

namespace App\Queries;

use App\Models\Visit;
use Illuminate\Database\Eloquent\Builder;

class VisitQuery
{
    public function getStatistics()
    {
        return Visit::query()
            ->selectRaw('
            COUNT(*) as total,
            SUM(visit_status = ?) as created,
            SUM(visit_status = ?) as completed,
            SUM(visit_status = ?) as cancelled
        ', ['created', 'completed', 'cancelled'])
            ->first();
    }
    public function getIndexQuery(Builder $query, array $filters = []): Builder
    {
        return $query
            ->when(
                $filters['patient_id'] ?? null,
                fn(Builder $query, $patient) => $query->where('patient_id', $patient)
            )
            ->when(
                $filters['doctor_id'] ?? null,
                fn(Builder $query, $doctor) =>  $query->where('doctor_id', $doctor)
            )
            ->when(
                ($filters['visit_status'] ?? null) === 'all'
                    ? null
                    : ($filters['visit_status'] ?? null),
                fn(Builder $query, $status) => $query->where('visit_status', $status)
            )
            ->when(
                $filters['date'] ?? null,
                fn(Builder $query, $date) => $query->whereDate('date', $date)
            )
            ->when(
                $filters['search'] ?? null,
                fn(Builder $query, $term) => $this->search($query, $term)
            );
    }

    private function search(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $query) use ($term) {
            $query->where('reason', 'like', "%{$term}%")
                ->orWhereHas('patient', function (Builder $query) use ($term) {
                    $query->where('fullname', 'like', "%{$term}%")
                        ->orWhere('mrn', 'like', "%{$term}%");
                })
                ->orWhereHas('doctor', function (Builder $query) use ($term) {
                    $query->where('fullname', 'like', "%{$term}%");
                })
                ->orWhereHas('creator', function (Builder $query) use ($term) {
                    $query->where('name', 'like', "%{$term}%");
                });
        });
    }
}
