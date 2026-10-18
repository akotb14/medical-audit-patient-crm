<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    //
    protected $table = 'clinics';
    protected $fillable = [
        'name',
        'description',
        'status'
    ];
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }
}
