<?php

namespace App\Http\Traits;

use Exception;
use App\Helpers\Helpers;
use App\Models\WalletBonus;
use App\Enums\TransactionType;
use App\Exceptions\ExceptionHandler;
use App\Http\Traits\WalletPointsTrait;
use App\Models\Transaction;

trait WalletBonusTrait
{
    use WalletPointsTrait;

    /**
     * Check if wallet bonus feature is enabled
     */

    public function updateBonusUsage(WalletBonus $walletBonus): void
    {
        if (!$walletBonus->is_unlimited && $walletBonus->total_usage_limit !== null) {
            $walletBonus->decrement('total_usage_limit');
        }
    }

    public function isBonusUsable(int $consumerId, WalletBonus $walletBonus): bool
    {
        if (!Helpers::walletBonusIsEnable()) {
            return false;
        }

        if ($walletBonus->is_unlimited) {
            return true;
        }

        if ($walletBonus->usage_limit_per_user) {
            $usedByUser = Helpers::getCountUsedPerUser($walletBonus->id, $consumerId);
            if ($usedByUser >= $walletBonus->usage_limit_per_user) {
                return false;
            }
        }

        if ($walletBonus->total_usage_limit !== null &&
            $walletBonus->total_usage_limit <= 0) {
            return false;
        }

        return true;
    }

    public function isWalletBonusEnabled(): bool
    {
        try {
            if (!Helpers::walletIsEnable()) {
                return false;
            }

            // Check if there are any active wallet bonus rules
            $activeBonuses = WalletBonus::where('status', 1)->exists();
            return $activeBonuses;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Find applicable wallet bonus rule based on top-up amount
     */
    public function findApplicableWalletBonus(float $topUpAmount): ?WalletBonus
    {
        try {
            if (!$this->isWalletBonusEnabled()) {
                return null;
            }

            // Find the best matching wallet bonus rule
            // Priority: highest min_top_up_amount that is <= topUpAmount
            $walletBonus = WalletBonus::where('status', 1)
                ->where('min_top_up_amount', '<=', $topUpAmount)
                ->orderBy('min_top_up_amount', 'desc')
                ->first();

            return $walletBonus;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Calculate bonus amount based on wallet bonus rule and top-up amount
     */
    public function calculateWalletBonus(WalletBonus $walletBonus, float $topUpAmount): float
    {
        try {
            $bonusAmount = 0;

            if ($walletBonus->type === 'fixed') {
                // Fixed bonus amount
                $bonusAmount = $walletBonus->bonus ?? 0;
            } elseif ($walletBonus->type === 'percentage') {
                // Percentage bonus
                $percentage = $walletBonus->bonus ?? 0;
                $bonusAmount = ($topUpAmount * $percentage) / 100;
            }

            // Apply max_bonus cap if set
            if ($walletBonus->max_bonus && $walletBonus->max_bonus > 0) {
                $bonusAmount = min($bonusAmount, $walletBonus->max_bonus);
            }

            return round($bonusAmount, 2);
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * Check if user is eligible for wallet bonus
     */
    public function isEligibleForWalletBonus(float $topUpAmount): bool
    {
        try {
            if (!$this->isWalletBonusEnabled()) {
                return false;
            }

            if ($topUpAmount <= 0) {
                return false;
            }

            $walletBonus = $this->findApplicableWalletBonus($topUpAmount);
            if (!$walletBonus) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Credit wallet bonus to user's wallet
     */
    public function creditWalletBonus(int $consumerId, float $topUpAmount, WalletBonus $walletBonus): bool
    {
        try {
            if (!$this->isEligibleForWalletBonus($topUpAmount)) {
                return false;
            }

            $bonusAmount = $this->calculateWalletBonus($walletBonus, $topUpAmount);

            if ($bonusAmount <= 0) {
                return false;
            }

            // Credit the bonus to wallet
            $wallet = $this->getWallet($consumerId);
            if ($wallet) {
                $wallet->increment('balance', $bonusAmount);
                
                // Record transaction with wallet_bonus_id
                $this->creditWalletBonusTransaction(
                    $wallet,
                    $bonusAmount,
                    "Wallet bonus for top-up of " . Helpers::getDefaultCurrencySymbol() . number_format($topUpAmount, 2),
                    $walletBonus->id
                );
            }

            return true;
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * Main entry: Credit wallet bonus after successful top-up
     */
    public function creditWalletBonusOnTopUp(int $consumerId, float $topUpAmount): bool
    {
        try {
            if (!$this->isWalletBonusEnabled()) {
                return false;
            }

            if ($topUpAmount <= 0) {
                return false;
            }

            $walletBonus = $this->findApplicableWalletBonus($topUpAmount);
            if (!$walletBonus) {
                return false;
            }

            if (!$this->isBonusUsable($consumerId, $walletBonus)) {
                return false;
            }
            
            $credited = $this->creditWalletBonus($consumerId, $topUpAmount, $walletBonus);

        if ($credited) {
            $this->updateBonusUsage($walletBonus);
        }

        return $credited;
        } catch (Exception $e) {
            // Don't throw exception, just return false to not break the top-up flow
            return false;
        }
    }

    /**
     * Record transaction with wallet bonus information
     */
    public function creditWalletBonusTransaction($wallet, float $amount, string $detail, int $walletBonusId)
    {
        return $wallet->transactions()->create([
            'amount' => $amount,
            'detail' => $detail,
            'type' => TransactionType::CREDIT,
            'wallet_bonus_id' => $walletBonusId,
            'wallet_bonus_amount' => $amount,
            'from' => $this->getAdminRoleId(),
        ]);
    }

    /**
     * Calculate adjusted top-up amount if bonus is admin funded
     * Returns array with 'adjusted_amount' and 'bonus_amount'
     * If is_admin_funded is true, user pays (original_amount - bonus_amount)
     */
    public function calculateAdjustedTopUpAmount(float $originalAmount): array
    {
        try {
            $walletBonus = $this->findApplicableWalletBonus($originalAmount);
            
            if (!$walletBonus) {
                return [
                    'adjusted_amount' => $originalAmount,
                    'bonus_amount' => 0,
                    'wallet_bonus' => null,
                    'is_admin_funded' => false,
                ];
            }

            $bonusAmount = $this->calculateWalletBonus($walletBonus, $originalAmount);
            
            // If bonus is admin funded, subtract bonus from user's payment
            if ($walletBonus->is_admin_funded == 1) {
                $adjustedAmount = max(0, $originalAmount - $bonusAmount);
                
                return [
                    'adjusted_amount' => round($adjustedAmount, 2),
                    'bonus_amount' => $bonusAmount,
                    'wallet_bonus' => $walletBonus,
                    'is_admin_funded' => true,
                    'original_amount' => $originalAmount,
                ];
            }

            // If not admin funded, user pays full amount, bonus is separate
            return [
                'adjusted_amount' => $originalAmount,
                'bonus_amount' => $bonusAmount,
                'wallet_bonus' => $walletBonus,
                'is_admin_funded' => false,
                'original_amount' => $originalAmount,
            ];
        } catch (Exception $e) {
            // On error, return original amount
            return [
                'adjusted_amount' => $originalAmount,
                'bonus_amount' => 0,
                'wallet_bonus' => null,
                'is_admin_funded' => false,
                'original_amount' => $originalAmount,
            ];
        }
    }
}

