<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\FollowUp;
use App\Models\User;

class FollowUpPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::FOLLOWUPS_VIEW->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FollowUp $followUp): bool
    {
        return $user->can(PermissionEnum::FOLLOWUPS_VIEW->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::FOLLOWUPS_CREATE->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FollowUp $followUp): bool
    {
        return $user->can(PermissionEnum::FOLLOWUPS_UPDATE->value);
    }

    public function complete(User $user, FollowUp $followUp): bool
    {
        return $user->can(PermissionEnum::FOLLOWUPS_COMPLETE->value);
    }

    public function status(User $user, FollowUp $followUp): bool
    {
        return $user->can(PermissionEnum::FOLLOWUPS_STATUS->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FollowUp $followUp): bool
    {
        return $user->can(PermissionEnum::FOLLOWUPS_DELETE->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FollowUp $followUp): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FollowUp $followUp): bool
    {
        return false;
    }
}
