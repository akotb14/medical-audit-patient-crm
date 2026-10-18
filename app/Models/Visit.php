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
        'created_by'
    ];
     protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function patient()
    {
        return $this->belongsTo(Patient::class , 'patient_id');
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class , 'doctor_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class , 'created_by');
    }
    public function followUp() :HasOne
    {
        return $this->hasOne(FollowUp::class);
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

    public function scopeForDate(Builder $query, $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('reason', 'like', "%{$term}%")
              ->orWhereHas('patient', function ($pq) use ($term) {
                  $pq->where('fullname', 'like', "%{$term}%")
                     ->orWhere('mrn', 'like', "%{$term}%");
              })
              ->orWhereHas('doctor', function ($dq) use ($term) {
                  $dq->where('fullname', 'like', "%{$term}%");
              })->orWhereHas('creator', function ($cq) use ($term){
                  $cq->where('name', 'like', "%{$term}%");
              });
        });
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
