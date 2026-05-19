<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ReferralBonus;

class ReferralBonusPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('backend.referral.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ReferralBonus $referralBonus)
    {
        if ($user->can('backend.referral.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->can('backend.referral.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ReferralBonus $referralBonus)
    {
        if ($user->can('backend.referral.edit')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ReferralBonus $referralBonus)
    {
        if ($user->can('backend.referral.destroy')) {
            return true;
        }
    }
}
