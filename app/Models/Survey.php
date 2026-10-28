<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'patient_id',
        'rating',
        'comment',
        'created_by',
        'updated_by',
    ];

    // relationship
    public function complaint()
    {
        return $this->hasOne(Complaint::class);
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient()
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

    // scopes
    public function scopeStats(Builder $query): Builder
    {
        return $query->selectRaw("
            count(*) AS total,
            AVG(rating) AS average_rating,
            COALESCE(SUM(CASE WHEN rating >= 3 THEN 1 ELSE 0 END),0 ) AS postive_ratings,
            COALESCE(SUM(CASE WHEN rating < 3 THEN 1 ELSE 0 END),0 ) AS negative_ratings,
            (
                SELECT COUNT(*)
                FROM complaints
                WHERE source = 'survey'
            ) AS complaints_count
        ");
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($query) use ($search) {
            $query
                ->orWhereHas('patient', function ($query) use ($search) {
                    $query->where('fullname', 'LIKE', '%'.$search.'%')
                        ->orWhere('mrn', 'like', '%'.$search.'%');
                });
        });
    }

    public function scopeIsLowRating(Builder $query): Builder
    {
        return $query->where('rating', '<', 3);
    }

    // attribute
    public function getRatingLabelAttribute(): string
    {
        return match ((int) $this->rating) {
            5 => 'ممتاز جداً (5/5)',
            4 => 'جيد جداً (4/5)',
            3 => 'مقبول (3/5)',
            2 => 'غير راضٍ (2/5) - تنبيه جودة',
            1 => 'سيء جداً (1/5) - شكوى حرجة',
            default => $this->rating.'/5',
        };
    }

    public function getRatingBadgeClassesAttribute(): string
    {
        return match ((int) $this->rating) {
            5 => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            4 => 'bg-teal-50 text-teal-800 border-teal-200',
            3 => 'bg-amber-50 text-amber-800 border-amber-200',
            2 => 'bg-orange-50 text-orange-800 border-orange-200',
            1 => 'bg-rose-50 text-rose-800 border-rose-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
