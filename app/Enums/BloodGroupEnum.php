<?php

namespace App\Enums;

enum BloodGroupEnum :string
{
    //
    case A_POSITIVE = 'A+';
    case A_NEGATIVE = 'A-';
    case B_POSITIVE = 'B+';
    case B_NEGATIVE = 'B-';
    case O_POSITIVE = 'O+';
    case O_NEGATIVE = 'O-';
    case AB_POSITIVE = 'AB+';
    case AB_NEGATIVE = 'AB-';
}