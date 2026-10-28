<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::COMPLAINTS_VIEW->value);
    }

    public function view(User $user, Complaint $complaint): bool
    {
        return $user->can(PermissionEnum::COMPLAINTS_VIEW->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::COMPLAINTS_CREATE->value);
    }

    public function update(User $user, Complaint $complaint): bool
    {
        return $user->can(PermissionEnum::COMPLAINTS_UPDATE->value);
    }

    public function assign(User $user, Complaint $complaint): bool
    {
        return $user->can(PermissionEnum::COMPLAINTS_ASSIGN->value);
    }
    public function advance(User $user, Complaint $complaint): bool
    {
       $permission =   $user->can(PermissionEnum::COMPLAINTS_ADVANCE->value);
       if($permission){
            return true;
       }
       if (!in_array($complaint->status,['new' , 'resolved' , 'closed' ]) && $user->id === $complaint->assigned_to ){

            return true;
       }
       return false;
    }
    public function reopen(User $user, Complaint $complaint): bool
    {
        return $user->can(PermissionEnum::COMPLAINTS_REOPEN->value);
    }

    public function delete(User $user, Complaint $complaint): bool
    {
        return $user->can(PermissionEnum::COMPLAINTS_DELETE->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Complaint $complaint): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Complaint $complaint): bool
    {
        return false;
    }
}
