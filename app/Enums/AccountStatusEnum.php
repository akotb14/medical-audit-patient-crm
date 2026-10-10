<?php

namespace App\Enums;

enum AccountStatusEnum :string
{
    //
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DELETED = 'deleted';
}