<?php

namespace App\Http\Traits;

use Exception;
use App\Models\User;
use App\Models\Booking;
use App\Helpers\Helpers;
use App\Models\ReferralBonus;
use App\Enums\BookingStatusReq;
use App\Exceptions\ExceptionHandler;
use App\Http\Traits\WalletPointsTrait;

trait ReferralTrait
{
  use WalletPointsTrait;

  /**
   * Apply referral code during user registration
   */
  public function applyReferralCode($referralCode, $newUser, string $userType = 'user'): bool
  {

    try {

      $cabSettings = Helpers::getSettings();
      if (!($cabSettings['activation']['referral_enable'] ?? false)) {
        return false;
      }

      if (!in_array($userType, ['provider', 'user'])) {
        throw new ExceptionHandler('Invalid user type. Must be user or provider.', 400);
      }

      if ($newUser->referral_code === $referralCode) {
        throw new ExceptionHandler('You cannot refer yourself.', 400);
      }

      $referrer = null;
      $referrer = User::where('referral_code', $referralCode)->where('status', true)->first();
      
      if (!$referrer) {
        throw new ExceptionHandler('Invalid referral code or referrer not found.', 400);
      }
      
      $referrerType = $referrer->hasRole('provider') ? 'provider' : 'user';
      
      if ($referrerType !== $userType) {
        throw new ExceptionHandler('Referral code belongs to a different user type.', 400);
      }

      $newUser->referred_by_id = $referrer->id;
      $newUser->save();
      ReferralBonus::create([
        'referrer_id' => $referrer->id,
        'referred_id' => $newUser->id,
        'referrer_type' => $userType,
        'referred_type' => $userType,
        'bonus_amount' => 0,
        'referred_bonus_amount' => 0,
        'booking_amount' => 0,
        'referrer_percentage' => 0,
        'referred_percentage' => 0,
        'status' => 'pending',
      ]);

      return true;

    } catch (Exception $e) {
      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Calculate bonus amounts based on ride and settings
   */
  public function calculateBonuses(float $bookingAmount, array $settings): array
  {
    try {
      if ($bookingAmount <= 0) {
        throw new ExceptionHandler('Booking amount must be greater than zero.', 400);
      }

      $referrerPercentage = $settings['referrer_bonus_percentage'] ?? 10;
      $referredPercentage = $settings['referred_bonus_percentage'] ?? 5;
      $referrerPercentage = max(0, min(100, $referrerPercentage));
      $referredPercentage = max(0, min(100, $referredPercentage));
      $referrerBonus = ($bookingAmount * $referrerPercentage) / 100;
      $referredBonus = ($bookingAmount * $referredPercentage) / 100;

      $result = [
        'referrer_bonus' => round($referrerBonus, 2),
        'referred_bonus' => round($referredBonus, 2),
        'referrer_percentage' => $referrerPercentage,
        'referred_percentage' => $referredPercentage,
      ];

      return $result;

    } catch (Exception $e) {

      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Check if referred user is eligible for bonus
   */
  public function isEligibleForReferralBonus($user, float $bookingAmount): bool
  {
    $userId = $user->id ?? null;
    $userType = $user instanceof User ? 'user' : 'provider';
    try {
      
      $cabSettings = Helpers::getSettings();
      $referralEnabled = $cabSettings['activation']['referral_enable'] ?? false;
      if (!$referralEnabled) {
        return false;
      }

      $minimumAmount = $cabSettings['referral_settings']['min_booking_amount'] ?? 250;
      if ($bookingAmount < $minimumAmount) {
        return false;
      }

      if (!$user->referred_by_id) {
        return false;
      }

      $completedStatusId = Helpers::getbookingStatusIdBySlug(BookingStatusReq::COMPLETED);
      if ($userType === 'user') {
        $completedBookingsCount = Booking::where('consumer_id', $user->id)->whereNotNull('parent_id')->where('booking_status_id', $completedStatusId)->count();
      } else {
        $completedBookingsCount = $user->bookings()->where('booking_status_id', $completedStatusId)->count();
      }

      if ($completedBookingsCount > 1) {
        return false;
      }
      
      $existingBonus = ReferralBonus::where('referred_id', $user->id)->where('referred_type', $userType)->where('status', 'credited')->exists();

      $eligible = !$existingBonus;
      return $eligible;

    } catch (Exception $e) {

      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Credit bonuses to both referrer and referred
   */
  public function creditBonuses($bonus): void
  {
    try {
      $symbol = $bonus?->currency_symbol;
      
      if ($bonus->referrer_type === 'user') {
        $this->creditWallet(
          $bonus->referrer_id,
          $bonus->bonus_amount,
          "Referral bonus for referring {$bonus->referred_type} (Booking: {$symbol}{$bonus->booking_amount})"
        );
      } else {
        $this->creditProviderWallet(
          $bonus->referrer_id,
          $bonus->bonus_amount,
          "Referral bonus for referring {$bonus->referred_type} (Booking: {$symbol}{$bonus->booking_amount})"
        );
      } 

      if ($bonus->referred_type === 'user') {
        $this->creditWallet(
          $bonus->referred_id,
          $bonus->referred_bonus_amount,
          "Welcome bonus for being referred by {$bonus->referrer_type} (Booking: {$symbol}{$bonus->booking_amount})"
        );
      } else {
        $this->creditProviderWallet(
          $bonus->referred_id,
          $bonus->referred_bonus_amount,
          "Welcome bonus for being referred by {$bonus->referrer_type} (Booking: {$symbol}{$bonus->booking_amount})"
        );
      }
      
      $bonus->update([
        'status' => 'credited',
        'credited_at' => now(),
      ]);

    } catch (Exception $e) {

      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Main entry: Credit referral bonus after ride completion
   */
  public function creditReferralBonus($ride, string $referredType = 'user'): bool
  {

    try {
      if(!$ride) {
        return false;
      }

      $cabSettings = Helpers::getSettings();
      $referralEnabled = $cabSettings['activation']['referral_enable'] ?? false;

      if (!$referralEnabled) {
        return false;
      }


      if($referredType === 'user') {
        $referredId = $ride->consumer_id;
        $referredUser = User::find($referredId);
      } else {
        $referredId = $ride->provider_id;
        $referredUser = User::find($referredId);
      }

      if (!$referredUser) {
        return false;
      }
      
      $minimumAmount = $cabSettings['referral_settings']['min_booking_amount'] ?? 250;
      $bookingAmount = $ride->subtotal;
      if ($bookingAmount > 0 && $bookingAmount < $minimumAmount) {
        return false;
      }

      if ($bookingAmount > 0 && !$this->isEligibleForReferralBonus($referredUser, $bookingAmount)) {
        return false;
      }

      $bonus = ReferralBonus::where('referred_id', $referredId)
        ->where('referred_type', $referredType)
        ->where('status', 'pending')
        ->first();

      if (!$bonus) {
        return false;
      }

      if ($bookingAmount > 0) {
        $referralSettings = $cabSettings['referral_settings'] ?? [];
        $bonuses = $this->calculateBonuses($bookingAmount, $referralSettings);
        $updateData = [
          'bonus_amount' => $bonuses['referrer_bonus'],
          'referred_bonus_amount' => $bonuses['referred_bonus'],
          'referrer_bonus_amount' => $bonuses['referrer_bonus'],
          'booking_amount' => $bookingAmount,
          'currency_symbol' => $ride?->currency_symbol ?? Helpers::getDefaultCurrencySymbol(),
          'referrer_percentage' => $bonuses['referrer_percentage'],
          'referred_percentage' => $bonuses['referred_percentage'],
        ];

        $bonus->update($updateData);
        $this->creditBonuses($bonus);
        return true;
      }

      return false;

    } catch (Exception $e) {

      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }
}
