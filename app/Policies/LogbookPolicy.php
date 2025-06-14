<?php

namespace App\Policies;

use App\Models\Logbook;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LogbookPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-logbooks');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Logbook $logbook): bool
    {
        return $user->can('view-logbooks');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-logbooks');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Logbook $logbook): bool
    {
        return $user->can('edit-logbooks');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Logbook $logbook): bool
    {
        return $user->can('delete-logbooks');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Logbook $logbook): bool
    {
        return $user->can('restore-logbooks');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Logbook $logbook): bool
    {
        return $user->can('forceDelete-logbooks');
    }
}
