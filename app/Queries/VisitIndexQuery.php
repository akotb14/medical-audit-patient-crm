<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;

class VisitIndexQuery
{
    public function stats(Builder $query)
    {
        return $query
            ->selectRaw('
            COUNT(*) as total,
            SUM(visit_status = ?) as created,
            SUM(visit_status = ?) as completed,
            SUM(visit_status = ?) as cancelled
        ', ['created', 'completed', 'cancelled'])
            ->first();
    }
}
