<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    //
    protected $fillable = [
        'fullname',
        'age',
        'gender',
        'phone',
        'address',
        'blood_group',
        'allergies',
        'chronic_diseases',
        'emergency_name',
        'emergency_phone',
        'status',
        'created_by',
        'updated_by',
    ];
}