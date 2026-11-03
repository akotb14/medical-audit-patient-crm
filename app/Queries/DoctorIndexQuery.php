<?php

namespace App\Queries;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Builder;

class DoctorIndexQuery
{
    public function build(array $filters): Builder
    {
        return Doctor::query()
            ->with('clinic:id,name')
            ->when($filters['search'] ?? null, function (
                Builder $query,
                string $search
            ) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('fullname', 'like', "%{$search}%")
                        ->orWhere('specialty', 'like', "%{$search}%");
                });
            })
            ->when($filters['clinic_id'] ?? null, function (
                Builder $query,
                int|string $clinicId
            ) {
                $query->where('clinic_id', $clinicId);
            })
            ->when($filters['status'] ?? null, function (
                Builder $query,
                string $status
            ) {
                $query->where('status', $status);
            })
            ->orderBy('id');
    }
}
