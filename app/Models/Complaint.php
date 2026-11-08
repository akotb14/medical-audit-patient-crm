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
        return $this->belongsTo(User::class, 'assigned_to')->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
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
