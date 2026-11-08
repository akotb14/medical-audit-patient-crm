<?php

namespace App\Queries;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\Builder;

class ComplaintQuery
{
    public function getIndexQuery(Builder $query, array $filters): Builder
    {

        if (array_key_exists('status', $filters) && $filters['status'] != 'all') {
            $query->where('status', $filters['status']);
        }

        if (array_key_exists('source', $filters) && $filters['source'] != 'all') {
            $query->where('source', $filters['source']);
        }

        if (array_key_exists('assigned_to', $filters) && $filters['assigned_to'] != 'all') {
            if ($filters['assigned_to'] === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $filters['assigned_to']);
            }
        }
        if (array_key_exists('search', $filters) && $filters['search'] != '') {
            $query->where(function ($query) use ($filters) {
                $query->orWhere('title', 'like', '%'.$filters['search'].'%')
                    ->orWhereHas('patient', function ($query) use ($filters) {
                        $query->orWhere('fullname', 'like', '%'.$filters['search'].'%');
                        $query->orWhere('phone', 'like', '%'.$filters['search'].'%');
                        $query->orWhere('mrn', 'like', '%'.$filters['search'].'%');
                    })->orWhereHas('assignee', function ($query) use ($filters) {
                        $query->orWhere('name', 'like', '%'.$filters['search'].'%');
                    });
            });
        }

        return $query;
    }

     public function getStatistics()
    {
        return Complaint::query()
            ->selectRaw("
            count(*) AS total,

            COALESCE(
            SUM(CASE WHEN status ='new' THEN 1 ELSE 0 END) , 0) AS new,

            COALESCE(
            SUM(CASE WHEN status ='assigned' THEN 1 ELSE 0 END) , 0) AS assigned,

            COALESCE(
            SUM(CASE WHEN status ='investigating' THEN 1 ELSE 0 END) , 0) AS investigating,

            COALESCE(
            SUM(CASE WHEN status ='resolved' THEN 1 ELSE 0 END) , 0) AS resolved,

            COALESCE(
            SUM(CASE WHEN status ='closed' THEN 1 ELSE 0 END) , 0) AS closed,

            COALESCE(
            SUM(CASE WHEN source = 'survey' THEN 1 ELSE 0 END) , 0) AS from_surveys,

            COALESCE(
            SUM(CASE WHEN assigned_to IS NULL THEN 1 ELSE 0 END) , 0) AS unassigned
        ")
            ->firstOrFail();
    }
}
