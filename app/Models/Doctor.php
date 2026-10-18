<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    //
    protected $fillable = [
        'fullname',
        'email',
        'phone',
        'specialty',
        'clinic_id',
        'schedule_days',
        'notes',
        'status'
    ];
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
    public function visits() :HasMany{
        return $this->hasMany(Visit::class);
    }
}