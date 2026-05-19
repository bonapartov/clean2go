<?php

namespace App\Http\Traits;

use Exception;
use App\Models\Wallet;
use App\Enums\RoleEnum;
use App\Models\Booking;
use App\Helpers\Helpers;
use App\Enums\ModuleEnum;
use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\ProviderWallet;
use App\Models\ServicemanWallet;
use App\Enums\WalletPointsDetail;
use App\Exceptions\ExceptionHandler;
use App\Models\PaymentTransactions;
use Nwidart\Modules\Facades\Module;

trait PaymentTrait
{
    use TransactionsTrait;
    use WalletBonusTrait;

    public function createPayment($booking, $request)
    {
        try {
            // Check if advance payment is enabled
            $isAdvanceEnabled = $booking->is_advance_payment_enabled ?? false;
            $originalTotal = $booking->total;
            
            // Determine payment amount: use request amount if set and valid, otherwise calculate
            $requestAmount = $request->amount ?? null;
            
            // Check if this is a remaining payment (amount matches remaining_payment_amount)
            $isRemainingPayment = false;
            if ($isAdvanceEnabled && $requestAmount && abs($requestAmount - $booking->remaining_payment_amount) < 0.01) {
                // This is remaining payment
                $paymentAmount = $booking->remaining_payment_amount;
                $isRemainingPayment = true;
            } elseif ($isAdvanceEnabled && $booking->advance_payment_status == 'PAID' && $booking->remaining_payment_status == 'PENDING') {
                // Advance is already paid, this must be remaining payment
                $paymentAmount = $booking->remaining_payment_amount;
                $isRemainingPayment = true;
            } elseif ($isAdvanceEnabled && $booking->advance_payment_amount > 0 && $booking->advance_payment_status == 'PENDING') {
                // Use advance payment amount (for initial advance payment)
                $paymentAmount = $booking->advance_payment_amount;
            } elseif ($requestAmount && $requestAmount < $originalTotal) {
                // Request amount is less than total, use it (might be advance from previous step)
                $paymentAmount = $requestAmount;
            } else {
                // Default to full amount
                $paymentAmount = $originalTotal;
            }

            if ($request->payment_method != 'cash' && $request->payment_method != 'wallet') {
                $module = Module::find($request->payment_method);
                if (!is_null($module) && $module?->isEnabled()) {
                    $moduleName = $module->getName();
                    $payment = 'Modules\\' . $moduleName . '\\Payment\\' . $moduleName;
                    if (class_exists($payment) && method_exists($payment, 'getIntent')) {
                        if ($request->type == 'extra_charge' && $booking->payment_method != PaymentMethod::COD) {
                            $total = 0;
                            $booking->payment_status = PaymentStatus::PENDING;
                            $booking->save();
                            $bookingOfExtraCharges = $booking->extra_charges()?->get();
                            foreach ($bookingOfExtraCharges as $extraCharge) {
                                $total += $extraCharge->grand_total;
                            }

                            $booking->total = $total;
                        } else {
                            // For advance payment, ensure booking has correct advance payment fields
                            // Payment gateways should use advance_payment_amount, but we also set total as fallback
                            if ($isAdvanceEnabled && $paymentAmount < $originalTotal) {
                                // Ensure advance payment fields are set correctly (they should already be set, but ensure they're correct)
                                if ($booking->advance_payment_amount != $paymentAmount) {
                                    $booking->advance_payment_amount = $paymentAmount;
                                }
                                if ($booking->remaining_payment_amount != ($originalTotal - $paymentAmount)) {
                                    $booking->remaining_payment_amount = $originalTotal - $paymentAmount;
                                }
                                if (!$booking->is_advance_payment_enabled) {
                                    $booking->is_advance_payment_enabled = true;
                                }
                                
                                // Temporarily set total as fallback (payment gateways might use this)
                                $booking->total = $paymentAmount;
                                $booking->save(); // Save before calling payment gateway
                                
                                // Get completely fresh instance to ensure all values are correct
                                $booking = Booking::find($booking->id);
                            }
                        }
                        
                        // Explicitly set amount in request to ensure payment gateway uses correct amount
                        // Set it in multiple ways to ensure it's accessible
                        $request->merge([
                            'type' => 'booking',
                            'amount' => $paymentAmount, // Pass the payment amount (advance or full)
                        ]);
                        
                        // Also set directly in request data to ensure it's accessible
                        if (method_exists($request, 'request')) {
                            $request->request->set('amount', $paymentAmount);
                        }
                        if (method_exists($request, 'query')) {
                            $request->query->set('amount', $paymentAmount);
                        }

                        $result = $payment::getIntent($booking, $request);

                        // Restore original total immediately after payment intent is created
                        // This ensures commission calculations use the full amount
                        if ($isAdvanceEnabled && $paymentAmount < $originalTotal && $request->type != 'extra_charge') {
                            $booking->total = $originalTotal;
                            $booking->save(); // Restore full amount immediately
                        }

                        return $result;
                    } else {
                        throw new Exception(__('static.booking.payment_module_not_found'), 400);
                    }
                }

                throw new Exception('Selected payment module not found or not enable.', 400);
            } elseif ($request->payment_method == 'cash') {
                $request->merge(['type' => 'booking']);
                // For cash with advance payment, both will be collected later
                if ($isAdvanceEnabled) {
                    $booking->advance_payment_status = 'PENDING';
                    $booking->remaining_payment_status = 'PENDING';
                    $booking->payment_status = PaymentStatus::PENDING;
                    $booking->save();
                    return ['status' => 'success', 'message' => 'Booking created. Payment will be collected after service completion.'];
                }
                return $this->paymentStatus($booking, PaymentStatus::PENDING, $request);
            } else if ($request->payment_method == 'wallet') {
                $request->merge(['type' => 'booking']);
                // For wallet with advance payment, debit only advance amount
                // Always set the amount in request to ensure paymentStatus receives correct amount
                if ($isAdvanceEnabled && $paymentAmount < $originalTotal) {
                    // This is advance payment - set advance amount
                    $request->merge(['amount' => $paymentAmount]);
                } elseif ($isAdvanceEnabled && $isRemainingPayment) {
                    // This is remaining payment - set remaining amount
                    $request->merge(['amount' => $paymentAmount]);
                } else {
                    // Full payment or no advance payment - set full amount
                    $request->merge(['amount' => $paymentAmount]);
                }
                return $this->paymentStatus($booking, $isAdvanceEnabled && $paymentAmount < $originalTotal ? 'PARTIAL' : PaymentStatus::COMPLETED, $request);
            }

            throw new Exception(__('static.booking.invalid_payment_method'), 400);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function paymentStatus($item, $status, $request)
    {
        if ($status) {
            if ($request->type == 'extra_charge' || $request->type == 'booking') {
                if ($item->payment_method == 'wallet') {
                    // Check if advance payment is enabled
                    $isAdvanceEnabled = $item->is_advance_payment_enabled ?? false;
                    $payableAmount = (float) ($request->amount ?? $item->total);

                    // If advance payment enabled and amount is less than total, it's advance payment
                    if ($isAdvanceEnabled && $payableAmount < $item->total) {
                        $status = 'PARTIAL';
                    } else {
                        $status = PaymentStatus::COMPLETED;
                    }

                    if ($request->discount_type && $request->discount_amount !== null) {
                        $discountType = strtolower(trim($request->discount_type));
                        $discountAmount = floatval($request->discount_amount);

                        if ($discountType === 'fixed') {
                            $payableAmount -= $discountAmount;
                        } elseif ($discountType === 'percentage') {
                            $payableAmount -= ($payableAmount * $discountAmount) / 100;
                        }

                        $payableAmount = max(0, $payableAmount);
               
                    }

                    $this->debitWallet($item->consumer_id, $payableAmount, WalletPointsDetail::WALLET_ORDER);
                }
                
                // Update payment status and advance payment status if applicable
                $updateData = ['payment_status' => $status];
                
                // If advance payment was made, update advance payment status
                if ($request->type == 'booking' && isset($item->is_advance_payment_enabled) && $item->is_advance_payment_enabled) {
                    $paymentAmount = (float) ($request->amount ?? $item->total);
                    
                    // Check if this is remaining payment (amount matches remaining_payment_amount)
                    $isRemainingPayment = false;
                    if (abs($paymentAmount - ($item->remaining_payment_amount ?? 0)) < 0.01 && $item->advance_payment_status == 'PAID') {
                        $isRemainingPayment = true;
                    }
                    
                    if ($isRemainingPayment) {
                        // This is remaining payment
                        $updateData['remaining_payment_status'] = 'PAID';
                        $updateData['payment_status'] = PaymentStatus::COMPLETED; // Full payment completed
                        $status = PaymentStatus::COMPLETED;
                        
                        // Update booking status to COMPLETED if it's currently ON_GOING
                        if ($item instanceof Booking && $item->booking_status && $item->booking_status->slug === \App\Enums\BookingEnumSlug::ON_GOING) {
                            $completedStatusId = \App\Helpers\Helpers::getbookingStatusId(\App\Enums\BookingEnum::COMPLETED);
                            if ($completedStatusId) {
                                $updateData['booking_status_id'] = $completedStatusId;
                            }
                        }
                    } elseif ($paymentAmount < $item->total) {
                        // This is advance payment only
                        $updateData['advance_payment_status'] = 'PAID';
                        $updateData['remaining_payment_status'] = 'PENDING';
                        $updateData['payment_status'] = 'PARTIAL'; // Force PARTIAL status for advance payment
                        $status = 'PARTIAL'; // Update status variable for consistency
                    } elseif ($paymentAmount >= $item->total) {
                        // This is full payment (both advance and remaining in one go)
                        $updateData['advance_payment_status'] = 'PAID';
                        $updateData['remaining_payment_status'] = 'PAID';
                        $updateData['payment_status'] = PaymentStatus::COMPLETED;
                        $status = PaymentStatus::COMPLETED; // Update status variable for consistency
                    }
                }

                // If item has a parent, update parent and all its sub-bookings
                if ($item->parent_id) {
                    $parent = Booking::findOrFail($item->parent_id);
                    
                    // Refresh parent to avoid relationship data in update
                    $parent = $parent->fresh();
                    
                    // Only update fillable fields - filter out any relationship data
                    $parentFillableFields = array_intersect_key($updateData, array_flip($parent->getFillable()));
                    
                    // Update parent with the same payment and advance payment status
                    $parent?->update($parentFillableFields);

                    // Update all sub-bookings of the parent (including the current item's siblings)
                    $parent?->sub_bookings()?->update($updateData);
                }

                // Refresh item to avoid relationship data in update
                $item = $item->fresh();
                
                // Only update fillable fields - filter out any relationship data
                $fillableFields = array_intersect_key($updateData, array_flip($item->getFillable()));
                $item?->update($fillableFields);

                $item?->sub_bookings()?->update($updateData);

                $item?->extra_charges()?->update([
                    'payment_status' => $status
                ]);
                
                // Process commission and referral if booking was completed via remaining payment
                if ($request->type == 'booking' && $item instanceof Booking && isset($updateData['booking_status_id'])) {
                    $completedStatusId = \App\Helpers\Helpers::getbookingStatusId(\App\Enums\BookingEnum::COMPLETED);
                    if ($updateData['booking_status_id'] == $completedStatusId) {
                        // Booking was auto-completed via payment, process commission
                        app(\App\Services\CommissionService::class)->handleCommission($item);
                        
                        // Process referral bonus (same logic as in repository update method)
                        $settings = \App\Helpers\Helpers::getSettings();
                        if ($settings['activation']['referral_enable'] ?? false) {
                            if ($item->subtotal > ($settings['referral_settings']['min_booking_amount'] ?? 0)) {
                                // Use repository to credit referral bonus (has ReferralTrait)
                                $bookingRepository = app(\App\Repositories\API\BookingRepository::class);
                                
                                $user = \App\Models\User::find($item->consumer_id);
                                if ($user && $user->referred_by_id) {
                                    $userCompletedBookings = \App\Models\Booking::where('consumer_id', $item->consumer_id)
                                        ->whereNotNull('parent_id')
                                        ->where('booking_status_id', $completedStatusId)
                                        ->count();
                                    if ($userCompletedBookings === 1) {
                                        $bookingRepository->creditReferralBonus($item, 'user');
                                    }
                                }

                                $provider = \App\Models\User::find($item->provider_id);
                                if ($provider && $provider->referred_by_id) {
                                    $providerCompletedBookings = $provider->bookings()
                                        ->where('booking_status_id', $completedStatusId)
                                        ->count();
                                    if ($providerCompletedBookings === 1) {
                                        $bookingRepository->creditReferralBonus($item, 'provider');
                                    }
                                }
                            }
                        }
                    }
                }

            } elseif ($request->type == 'wallet') {
                if ($status == PaymentStatus::COMPLETED) {
                    // Get paid amount (this is the adjusted amount if admin funded)
                    // $request->amount is the original amount from frontend (unchanged)
                    // But payment gateway stores the adjusted amount in PaymentTransactions
                    $paidAmount = $request->amount; // This is what payment gateway charged
                    
                    // Get original amount and bonus info from request (set during topUp in WalletRepository)
                    $originalAmount = $request->original_topup_amount ?? $paidAmount;
                    $isAdminFunded = $request->is_admin_funded ?? false;
                    $bonusAmount = $request->wallet_bonus_amount ?? null;
                    $walletBonusId = $request->wallet_bonus_id ?? null;
                    
                    // If original amount not in request (e.g., from verifyPayment callback),
                    // we need to recalculate: original = paid + bonus (if admin funded)
                    if (!$originalAmount || ($originalAmount == $paidAmount && $item instanceof \App\Models\Wallet && isset($item->consumer_id))) {
                        // Try to find if this was an admin-funded scenario
                        // Test with paidAmount + potential bonus amounts
                        $potentialBonuses = [10, 20, 50, 100, 200, 500]; // Common bonus amounts
                        foreach ($potentialBonuses as $testBonus) {
                            $testOriginal = $paidAmount + $testBonus;
                            $adjustment = $this->calculateAdjustedTopUpAmount($testOriginal);
                            if ($adjustment['is_admin_funded'] && 
                                abs($adjustment['adjusted_amount'] - $paidAmount) < 0.01 &&
                                abs($adjustment['bonus_amount'] - $testBonus) < 0.01) {
                                $originalAmount = $testOriginal;
                                $bonusAmount = $adjustment['bonus_amount'];
                                $walletBonusId = $adjustment['wallet_bonus']->id ?? null;
                                $isAdminFunded = true;
                                break;
                            }
                        }
                        
                        // If still not found, use paidAmount as original (no admin funded bonus)
                        if (!$originalAmount || $originalAmount == $paidAmount) {
                            $originalAmount = $paidAmount;
                        }
                    }
                    
                    // Credit the amount user actually paid (adjusted amount if admin funded)
                    // The payment gateway charged the adjusted amount, so we credit that
                    $item->increment('balance', $paidAmount);
                    $this->storeTransaction($item, TransactionType::CREDIT, WalletPointsDetail::TOPUP, $paidAmount);
                    
                    // Credit wallet bonus if applicable (only for consumer wallets)
                    if ($item instanceof \App\Models\Wallet && isset($item->consumer_id)) {
                        // If admin funded, credit the bonus amount separately
                        if ($isAdminFunded && $bonusAmount > 0 && $walletBonusId) {
                            $item->increment('balance', $bonusAmount);
                            $this->creditWalletBonusTransaction(
                                $item,
                                $bonusAmount,
                                "Wallet bonus (admin funded) for top-up of " . Helpers::getDefaultCurrencySymbol() . number_format($originalAmount, 2),
                                $walletBonusId
                            );
                        } else {
                            // Normal bonus flow (not admin funded) - bonus is separate credit
                            $this->creditWalletBonusOnTopUp($item->consumer_id, $originalAmount);
                        }
                    }
                }
            } elseif($request->type == 'subscription') {
                if($status == PaymentStatus::COMPLETED){
                    $item?->update([
                        'payment_status' => $status,
                        'is_active' => true,
                    ]);
                } else {
                    $item?->update([
                        'payment_status' => $status,
                    ]);
                }
            }
        }

        $item = $item?->fresh();
        return $item;
    }

    public function verifyPayment(Request $request)
    {
        try {

            if ($request->type == 'extra_charge') {
                $request->merge(['is_type' => 'extra_charge']);
                $request->merge(['type' => 'booking']);
            }

            if($request->type == 'booking'){
                $item = Booking::findOrFail($request->item_id);
                if($item->payment_status == 'cash'){
                    $payment_status = PaymentStatus::COMPLETED;
                    $transactions = $this->paymentStatus($item, $payment_status, $request);
                }
            }

            $paymentTransaction = self::getPaymentTransactions($request->item_id, $request?->type);
            
            if ($paymentTransaction) {
                $payment_method = $paymentTransaction?->payment_method;
                switch ($paymentTransaction?->type) {
                    case 'wallet':
                        $currentRoleName = Helpers::getCurrentRoleName();
                        if ($currentRoleName === RoleEnum::SERVICEMAN) {
                            $item = ServicemanWallet::findOrFail($request->item_id);
                        } else if ($currentRoleName === RoleEnum::PROVIDER) {
                            $item = ProviderWallet::findOrFail($request->item_id);
                        } else {
                            $item = Wallet::findOrFail($request->item_id);
                        }
                        break;
                    case 'subscription':
                        $item = $this->getSubscription($request->item_id);
                        break;
                    case 'booking' || 'extra_charge':
                        $item = Booking::findOrFail($request->item_id);
                        
                        // Link payment transaction to booking via pivot table
                        if ($item instanceof Booking && $paymentTransaction) {
                            $paymentType = 'full';
                            if ($item->is_advance_payment_enabled) {
                                // Check if this is advance payment (amount < total)
                                if ($paymentTransaction->amount < $item->total) {
                                    $paymentType = 'advance';
                                } else {
                                    $paymentType = 'remaining';
                                }
                            }
                            
                            // Attach payment transaction to booking
                            if (!$item->payment_transactions()->where('payment_transaction_id', $paymentTransaction->id)->exists()) {
                                $item->payment_transactions()->attach($paymentTransaction->id, [
                                    'payment_type' => $paymentType
                                ]);
                            }
                            
                            // Update transaction_ids in booking
                            $transactionIds = $item->transaction_ids ?? [];
                            if (!in_array($paymentTransaction->transaction_id, $transactionIds)) {
                                $transactionIds[] = $paymentTransaction->transaction_id;
                                $item->transaction_ids = $transactionIds;
                                $item->save();
                            }
                        }
                        break;
                }

                if ($item && $payment_method) {
                    if ($payment_method != PaymentMethod::COD && $payment_method != 'wallet') {
                        $payment = Module::find($payment_method);
                        if (!is_null($payment) && $payment?->isEnabled()) {
                            $request['amount'] = $paymentTransaction?->amount;
                            $payment_status = $paymentTransaction?->payment_status;
                            
                            // Check if this is an advance payment for booking
                            if ($request->type == 'booking' && $item instanceof Booking) {
                                $isAdvanceEnabled = $item->is_advance_payment_enabled ?? false;
                                $paymentAmount = (float) ($paymentTransaction?->amount ?? $item->total);
                                
                                // Check if this is remaining payment (amount matches remaining_payment_amount)
                                $isRemainingPayment = false;
                                if ($isAdvanceEnabled && 
                                    $item->advance_payment_status == 'PAID' && 
                                    abs($paymentAmount - ($item->remaining_payment_amount ?? 0)) < 0.01) {
                                    $isRemainingPayment = true;
                                    $payment_status = PaymentStatus::COMPLETED; // Remaining payment completes full payment
                                    // Set amount in request for paymentStatus to recognize it as remaining payment
                                    $request->merge(['amount' => $paymentAmount]);
                                } elseif ($isAdvanceEnabled && $paymentAmount < $item->total) {
                                    // This is advance payment only
                                    $payment_status = 'PARTIAL';
                                } elseif ($isAdvanceEnabled && $paymentAmount >= $item->total) {
                                    // Full payment (both advance and remaining in one go)
                                    $payment_status = PaymentStatus::COMPLETED;
                                }
                            }
                            
                            $transactions =  $this->paymentStatus($item, $payment_status, $request);
                            if ($paymentTransaction?->request_type == 'web' && $paymentTransaction?->type == 'booking') {
                                return redirect()->route('frontend.booking.index');
                            }

                            if ($paymentTransaction?->request_type == 'web' && $paymentTransaction?->type == 'wallet') {
                                return redirect()->route('frontend.account.wallet');
                            }

                            return $transactions;
                        }
                    } else if ($payment_method  == 'wallet' || $payment_method  == PaymentMethod::COD) {
                        $payment_status = PaymentStatus::COMPLETED;
                        $transactions = $this->paymentStatus($item, $payment_status, $request);
                        if ($paymentTransaction?->request_type == 'web') {
                            return redirect()->route('frontend.booking.index');
                        }

                        return $transactions;
                    }

                    throw new Exception(__('static.booking.payment_method_not_found'), 400);
                }
            }

            throw new Exception(__('static.booking.invalid_details'), 400);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getSubscription($item_id)
    {
        $module = Module::find(ModuleEnum::SUBSCRIPTION);
        if (!is_null($module) && $module?->isEnabled()) {
            return $this->UserSubscription?->findOrFail($item_id);
        }

        throw new Exception('Subscription module is inactive.', 400);
    }

    public static function updatePaymentStatus($payment, $status)
    {
        if ($payment) {
            $payment?->update([
                'payment_status' => $status,
            ]);

            $payment = $payment?->fresh();
            if ($payment->request_type == 'web') {
                $request = new Request();
                $request->merge(['item_id' => $payment->item_id]);
                $request->merge(['type' => $payment->type]);
                $instance = new self();

                return $instance->verifyPayment($request);
            }

            return $payment;
        }
    }

    public static function updatePaymentMethod($booking, $method)
    {
        $booking?->update([
            'payment_method' => $method,
        ]);

        $booking = $booking->fresh();
        return $booking;
    }

    public static function verifyTransaction($transaction_id)
    {
        return PaymentTransactions::where('transaction_id', $transaction_id)->first();
    }

    public static function getPaymentTransactions($item_id, $type)
    {

        return PaymentTransactions::where([
            'item_id' => $item_id,
            'type' => $type,
        ])?->first();
    }

    public static function updatePaymentStatusByType($item_id, $type, $status)
    {
        $payment = self::getPaymentTransactions($item_id, $type);

        return self::updatePaymentStatus($payment, $status);
    }

    public static function updatePaymentStatusByTrans($transaction_id, $status)
    {
        $payment = self::verifyTransaction($transaction_id);

        return self::updatePaymentStatus($payment, $status);
    }

    public static function updatePaymentTransactionId($payment, $transaction_id)
    {
        if ($payment) {
            $payment?->update([
                'transaction_id' => $transaction_id,
            ]);

            $payment = $payment?->fresh();

            return $payment;
        }
    }
}
