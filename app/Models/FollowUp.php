<?php

namespace App\Models;

use Database\Factories\FollowUpFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    /** @use HasFactory<FollowUpFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $table = 'follow_ups';

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'مجدولة (قيد الانتظار)',
            'done' => 'مكتملة وموثقة',
            'missed' => 'فائتة / غير مجابة',
            'cancelled' => 'ملغاة',
            default => $this->status ?? 'غير محدد',
        };
    }

    /**
     * تنسيقات شارات الحالة (Tailwind CSS)
     */
    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'done' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'missed' => 'bg-rose-50 text-rose-700 border border-rose-200',
            'cancelled' => 'bg-slate-100 text-slate-600 border border-slate-200',
            default => 'bg-slate-50 text-slate-700 border border-slate-200',
        };
    }

    /**
     * التحقق مما إذا كانت المتابعة قد تجاوزت موعد استحقاقها دون إتمام
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'scheduled' && $this->due_date && $this->due_date->isPast();
    }

    /**
     * Scopes لتصفية الحالات
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDone(Builder $query): Builder
    {
        return $query->where('status', 'done');
    }

    public function scopeMissed(Builder $query): Builder
    {
        return $query->where('status', 'missed');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->where('due_date', '<', now());
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('reason', 'like', "%{$term}%")
                ->orWhere('outcome_note', 'like', "%{$term}%")
                ->orWhereHas('patient', function (Builder $pq) use ($term) {
                    $pq->where('fullname', 'like', "%{$term}%")
                        ->orWhere('mrn', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
        });
    }
}
