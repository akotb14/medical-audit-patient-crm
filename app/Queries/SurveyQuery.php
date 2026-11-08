<?php

namespace App\Queries;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Builder;

class SurveyQuery
{
    public function getIndexQuery(Builder $query, array $filters): Builder
    {
        $search = $filters['search'] ?? '';
        $rating = (int) ($filters['rating'] ?? 0);

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->orWhereHas('patient', function ($query) use ($search) {
                        $query->where('fullname', 'LIKE', '%' . $search . '%')
                            ->orWhere('mrn', 'like', '%' . $search . '%');
                    });
            });
        }
        if ($rating > 0 && $rating <= 5) {
            $query->where('rating', $rating);
        }
        return $query;
    }

    public function getStatistics()
    {
        return Survey::query()
            ->selectRaw("
            count(*) AS total,
            AVG(rating) AS average_rating,
            COALESCE(SUM(CASE WHEN rating >= 3 THEN 1 ELSE 0 END),0 ) AS postive_ratings,
            COALESCE(SUM(CASE WHEN rating < 3 THEN 1 ELSE 0 END),0 ) AS negative_ratings,
            (
                SELECT COUNT(*)
                FROM complaints
                WHERE source = 'survey'
            ) AS complaints_count
        ")
            ->firstOrFail();
    }
}
