<?php

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fullname',
        'birthdate',
        'gender',
        'phone',
        'address',
        'blood_group',
        'allergies',
        'chronic_diseases',
        'emergency_name',
        'emergency_phone',
        'status',
        'mrn',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by')->withTrashed();
    }

    public function scopeStats(Builder $query): Builder
    {
        return $query
            ->selectRaw("
            COUNT(*) AS total,

            COUNT(CASE
                WHEN allergies IS NOT NULL
                 AND TRIM(allergies) != ''
                THEN 1
            END) AS allergies_count,

            COUNT(CASE
                WHEN chronic_diseases IS NOT NULL
                 AND TRIM(chronic_diseases) != ''
                THEN 1
            END) AS chronic_diseases_count

            (
                SELECT blood_group
                FROM patients
                WHERE deleted_at IS NULL
                  AND blood_group IS NOT NULL
                  AND TRIM(blood_group) != ''
                GROUP BY blood_group
                ORDER BY COUNT(*) DESC, blood_group ASC
                LIMIT 1
            ) AS most_common_blood_group
        ");
    }
}
