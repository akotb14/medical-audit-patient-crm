<?php

namespace App\Models;

use Database\Factories\ComplaintFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    /** @use HasFactory<ComplaintFactory> */
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'survey_id',
        'source',
        'title',
        'description',
        'status',
        'assigned_to',
        'resolution_note',
        'resolved_at',
        'closed_at',
        'created_by',
        'updated_by',

    ];

    protected function casts()
    {
        return [
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    // relations
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id')->withTrashed();
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class, 'survey_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // attributes

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'جديدة (New)',
            'assigned' => 'مسندة للتحقيق (Assigned)',
            'investigating' => 'قيد التدقيق والتحقيق (Investigating)',
            'resolved' => 'تم الحل والمعالجة (Resolved)',
            'closed' => 'مغلقة نهائياً (Closed)',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'new' => 'bg-blue-50 text-blue-800 border-blue-200',
            'assigned' => 'bg-purple-50 text-purple-800 border-purple-200',
            'investigating' => 'bg-amber-50 text-amber-800 border-amber-200',
            'resolved' => 'bg-teal-50 text-teal-800 border-teal-200',
            'closed' => 'bg-slate-100 text-slate-700 border-slate-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'survey' => 'استبيان رضا (تلقائي)',
            'manual' => 'تسجيل يدوي (مباشر)',
            default => $this->source,
        };
    }

    public function getSourceBadgeClassesAttribute(): string
    {
        return match ($this->source) {
            'survey' => 'bg-rose-50 text-rose-800 border-rose-200',
            'manual' => 'bg-sky-50 text-sky-800 border-sky-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    // scopes

    public function scopeStats(Builder $query)
    {
        return $query->selectRaw("
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
        ");
    }

    public function scopeFilter(Builder $query, array $filters)
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

    public function nextStatus(): ?string
    {
        return match ($this->status) {
            'new' => 'assigned',
            'assigned' => 'investigating',
            'investigating' => 'resolved',
            'resolved' => 'closed',
            default => null,
        };
    }

    public function canReopen(): bool
    {
        return in_array(
            $this->status,
            ['resolved', 'closed'],
            true
        );
    }
}
