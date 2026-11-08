<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    //
    use HasFactory;

    protected $table = 'visits';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'date',
        'reason',
        'visit_status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id')->withTrashed();
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id')->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    public function followUp(): HasOne
    {
        return $this->hasOne(FollowUp::class);
    }

    public function survey(): HasOne
    {
        return $this->hasOne(Survey::class);
    }

    /**
     * Scopes لتصفية الحالات
     */
    public function scopeCreatedStatus(Builder $query): Builder
    {
        return $query->where('visit_status', 'created');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('visit_status', 'completed');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('visit_status', 'cancelled');
    }

    // attributes
    public function getStatusLabelAttribute(): string
    {
        return match ($this->visit_status) {
            'created' => 'تم الإنشاء / قيد الانتظار',
            'completed' => 'مكتملة بنجاح',
            'cancelled' => 'ملغاة',
            default => $this->visit_status ?? 'غير محدد',
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->visit_status) {
            'created' => 'bg-blue-50 text-blue-700 border-blue-200 ring-1 ring-blue-300',
            'completed' => 'bg-emerald-50 text-emerald-800 border-emerald-200 ring-1 ring-emerald-300',
            'cancelled' => 'bg-rose-50 text-rose-800 border-rose-200 ring-1 ring-rose-300',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
