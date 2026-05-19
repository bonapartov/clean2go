<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserDocument;

class ProviderDocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->can('backend.provider_document.index');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserDocument $userDocument)
    {
        return $user->can('backend.provider_document.index');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->can('backend.provider_document.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserDocument $userDocument)
    {
        if (!$user->can('backend.provider_document.edit')) {
            return false;
        }

        if ($user->hasRole('provider')) {
            return $userDocument->user_id === $user->id;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model. 
     */
    public function delete(User $user, UserDocument $userDocument)
    {
        return $user->can('backend.provider_document.destroy');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserDocument $userDocument)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserDocument $userDocument)
    {
        //
    }
}