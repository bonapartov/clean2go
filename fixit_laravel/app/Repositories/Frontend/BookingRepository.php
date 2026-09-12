<?php

namespace App\Repositories\Frontend;

use Exception;
use App\Models\Booking;
use App\Helpers\Helpers;
use App\Enums\BookingEnum;
use App\Enums\PaymentStatus;
use App\Models\User;
use App\Enums\BookingEnumSlug;
use App\Enums\WalletPointsDetail;
use App\Events\CreateBookingEvent;
use App\Http\Traits\BookingTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\ReferralTrait;
use App\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;

class BookingRepository extends BaseRepository
{
  use BookingTrait, ReferralTrait;

  public function model()
  {
    return Booking::class;
  }

  public function store($request)
  {
    DB::beginTransaction();

    try {
      $cartItems = session('cartItems', []);
      if (!count($cartItems)) {
        throw new Exception('cart is empty', 400);
      }

      $payload = $this->generateCheckoutPayload($cartItems, $request);
      $booking = $this->placeBooking($payload);

      $booking = $booking->fresh();
      DB::commit();

      // Set the payment amount explicitly for advance payment
      $isAdvanceEnabled = $booking->is_advance_payment_enabled ?? false;
      $paymentAmount = $booking->total; // Default to full amount
      
      if ($isAdvanceEnabled && $booking->advance_payment_amount > 0) {
        $paymentAmount = $booking->advance_payment_amount;
      }
      
      // Explicitly set the amount in the payload
      $payload->merge([
        'amount' => $paymentAmount,
        'type' => 'booking'
      ]);

      $payment = $this->createPayment($booking, $payload);

      if (isset($payment['is_redirect']) || $request->payment_method  == 'cash' || $request->payment_method  == 'wallet') {
        $request->session()->forget('cartItems');
        $request->session()->forget('checkout');
        $request->session()->forget('service_bookings');
        $request->session()->forget('service_package_bookings');
        $request->session()->forget('coupon');
        if ($payment['is_redirect']) {
          return redirect()->away($payment['url']);
        }
      }

      return to_route('frontend.booking.index');

    } catch (Exception $e) {

      DB::rollback();
      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }

  public function update($request, $id)
  {
    $bookingStatus = Helpers::getBookingIdBySlug($request['booking_status']);
    $settings = Helpers::getSettings();
    DB::beginTransaction();
    try {

      // Load booking without eager loading relationships to avoid relationship data in attributes
      $booking = $this->model->findOrFail($id);
      // Refresh to clear any relationship data that might be in attributes
      $booking = $booking->fresh();
      
      if ($booking->booking_status_id === Helpers::getbookingStatusId(BookingEnum::PENDING)) {
        if (isset($request['address_id'])) {
          $this->assertAddressWithinProviderZone($booking->provider_id, $request['address_id']);
          $booking->address_id = $request['address_id'] ?? $booking->address_id;
          $booking->save();
        }

        if ((isset($request['date']) && isset($request['time']))) {
          $dateTime = $request['date'] . " " . $request['time'];
          $booking->date_time = $dateTime ?? $booking->date_time;
          $booking->save();
        }
      }

      if($bookingStatus->slug === BookingEnumSlug::COMPLETED){
        // Handle remaining payment collection for advance payment enabled bookings
        if ($booking->is_advance_payment_enabled && 
            $booking->remaining_payment_status == 'PENDING' && 
            $booking->remaining_payment_amount > 0) {
            
            $this->collectRemainingPayment($booking);
        }
        
        // Only process commission and referral after full payment is received
        $isFullyPaid = $booking->payment_status == PaymentStatus::COMPLETED || 
                      ($booking->advance_payment_status == 'PAID' && $booking->remaining_payment_status == 'PAID');
        
        if ($isFullyPaid) {
            app(\App\Services\CommissionService::class)->handleCommission($booking);
            if ($settings['activation']['referral_enable'] ?? false) {
                if ($booking->subtotal > $settings['referral_settings']['min_booking_amount']) {
                    $user = User::find($booking->consumer_id);
                    if ($user && $user->referred_by_id) {
                        $userCompletedBookings = Booking::where('consumer_id', $booking->consumer_id)->whereNotNull('parent_id')->where('booking_status_id', $booking->booking_status_id)->count();
                        if ($userCompletedBookings === 1) {
                            $this->creditReferralBonus($booking, 'user');
                        }
                    }
                    $provider = User::find($booking->provider_id);
                    if ($provider && $provider->referred_by_id) {
                        $providerCompletedBookings = $provider->bookings()->where('booking_status_id', $booking->booking_status_id)?->count();
                        if ($providerCompletedBookings === 1) {
                            $this->creditReferralBonus($booking, 'provider');
                        }
                    }
                }
            }
        }
      }

      $this->updateBookingStatusLogs($request, $booking);
      DB::commit();
      return redirect()->back()->with("message", "Booking update successfully");
    } catch (Exception $e) {

      DB::rollback();
      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }

  public function payment($request)
  {
    try {

      if ($request->booking_id) {
        $booking = $this->model->findOrFail($request->booking_id);
        $request->merge([
          'payment_method' => $booking?->payment_method,
          'type' => 'extra_charge',
          'request_type' => 'web'
        ]);

        $payment = $this->createPayment($booking, $request);
        if(isset($booking->parent_id)){
            event(new CreateBookingEvent($booking));
        }
        
        if (isset($payment['is_redirect'])) {
          if ($payment['is_redirect']) {
            return redirect()->away($payment['url']);
          }
        }

        return to_route('frontend.booking.index');
      }

    } catch (Exception $e) {

      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Pay remaining payment for advance payment enabled bookings
   */
  public function payRemainingPayment($request, $id)
  {
    try {
      $booking = $this->model->findOrFail($id);
      
      // Verify that this booking has advance payment enabled and remaining payment is pending
      if (!$booking->is_advance_payment_enabled) {
        throw new Exception('This booking does not have advance payment enabled.', 400);
      }
      
      if ($booking->remaining_payment_status != 'PENDING') {
        throw new Exception('Remaining payment is already paid or not applicable.', 400);
      }
      
      if ($booking->remaining_payment_amount <= 0) {
        throw new Exception('No remaining payment amount to collect.', 400);
      }
      
      // Set the amount to remaining payment amount
      $request->merge([
        'amount' => $booking->remaining_payment_amount,
        'type' => 'booking',
        'payment_method' => $booking->payment_method,
        'request_type' => 'web'
      ]);
      
      // Create payment for remaining amount
      $payment = $this->createPayment($booking, $request);
      
      if (isset($payment['is_redirect'])) {
        if ($payment['is_redirect']) {
          return redirect()->away($payment['url']);
        }
      }
      
      return redirect()->back()->with('message', 'Remaining payment processed successfully.');
      
    } catch (Exception $e) {
      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Collect remaining payment after service completion
   */
  private function collectRemainingPayment($booking)
  {
    try {
      $remainingAmount = $booking->remaining_payment_amount;
      $paymentMethod = $booking->payment_method;

      if ($paymentMethod == 'wallet') {
        // Auto-debit remaining amount from wallet
        $this->debitWallet($booking->consumer_id, $remainingAmount, WalletPointsDetail::WALLET_ORDER);

        // Update booking payment status
        $booking->remaining_payment_status = 'PAID';
        $booking->payment_status = PaymentStatus::COMPLETED;
        $booking->save();
      } elseif ($paymentMethod == 'cash') {
        // For cash, serviceman/admin will mark as paid manually
        // Status remains PENDING until manually updated
        // This will be handled by admin/provider marking payment as received
      } else {
        // Card/UPI - Send payment link or auto-charge
        // For now, mark for manual collection
        // You can implement payment link generation here
        // The remaining payment will be collected via a separate payment flow
      }
    } catch (Exception $e) {
      // Log error but don't fail the booking completion
      Log::error('Failed to collect remaining payment: ' . $e->getMessage());
    }
  }
}
