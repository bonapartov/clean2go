<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WalletBonus;

class WalletBonusPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('backend.wallet_bonus.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WalletBonus $walletBonus)
    {
        if ($user->can('backend.wallet_bonus.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->can('backend.wallet_bonus.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WalletBonus $walletBonus)
    {
        if ($user->can('backend.wallet_bonus.edit')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WalletBonus $walletBonus)
    {
        if ($user->can('backend.wallet_bonus.destroy')) {
            return true;
        }
    }
}
