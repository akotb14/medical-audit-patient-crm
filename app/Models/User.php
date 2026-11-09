<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'username', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable,SoftDeletes;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function roleNames(): string
    {
        return $this->getRoleNames()->implode(', ');
    }

    /**
     * The roles of this user as one label for the Arabic interface.
     *
     * Role names are stored in Latin, so each one is resolved through
     * `lang/ar/roles.php` and falls back to the stored name when that file has
     * no entry for it. The fallback is used when the user carries no role.
     */
    public function roleLabels(string $fallback = 'بدون دور'): string
    {
        $labels = $this->getRoleNames()
            ->map(fn (string $role): string => trans()->has('roles.'.$role)
                ? (string) __('roles.'.$role)
                : $role)
            ->filter()
            ->implode('، ');

        return $labels !== '' ? $labels : $fallback;
    }
}
