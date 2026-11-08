<?php

namespace App\Queries;

use App\Models\FollowUp;
use Illuminate\Database\Eloquent\Builder;

class FollowUpQuery
{
    public function getIndexQuery(Builder $query, array $filters): Builder
    {
        $status = $filters['status'] ?? 'all';
        $search = $filters['search'] ?? null;
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }


        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('outcome_note', 'like', "%{$search}%")
                    ->orWhereHas('patient', function (Builder $pq) use ($search) {
                        $pq->where('fullname', 'like', "%{$search}%")
                            ->orWhere('mrn', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }
        return $query;
    }

     public function getStatistics()
    {
        return FollowUp::query()
            ->selectRaw('
            COUNT(*) AS total,

            COALESCE(SUM(CASE
                WHEN status = ? THEN 1 ELSE 0
            END), 0) AS scheduled,

            COALESCE(SUM(CASE
                WHEN status = ? THEN 1 ELSE 0
            END), 0) AS done,

            COALESCE(SUM(CASE
                WHEN status = ? THEN 1 ELSE 0
            END), 0) AS missed,

            COALESCE(SUM(CASE
                WHEN status = ? THEN 1 ELSE 0
            END), 0) AS cancelled,

            COALESCE(SUM(CASE
                WHEN status = ? AND due_date < ? THEN 1 ELSE 0
            END), 0) AS overdue
        ', [
                'scheduled',
                'done',
                'missed',
                'cancelled',
                'scheduled',
                now(),
            ])
            ->firstOrFail();
    }
}
