<?php

namespace App\Repositories\API;

use Exception;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\RecurringBooking;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Traits\BookingTrait;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class RecurringBookingRepository extends BaseRepository
{
    use BookingTrait;

    public function model()
    {
        return RecurringBooking::class;
    }

    /**
     * Create a recurring booking record
     * Individual bookings will be created by cron job 1 day before scheduled date
     */
    public function createRecurringBooking($request, $calculatedCosts)
    {
        DB::beginTransaction();
        
        try {
            $userId = Helpers::getCurrentUserId();
            $frequency = $request->frequency; // weekly, monthly, yearly
            
            // Calculate next booking date based on frequency from start_date
            $startDate = Carbon::parse($request->start_date);
            $firstBookingDateTime = Carbon::parse($request->date_time);
            
            // Calculate next booking date (for cron to check)
            $nextBookingDate = null;
            switch ($frequency) {
                case 'weekly':
                    $nextBookingDate = $startDate->copy()->addWeek();
                    break;
                case 'monthly':
                    $nextBookingDate = $startDate->copy()->addMonth();
                    break;
                case 'yearly':
                    $nextBookingDate = $startDate->copy()->addYear();
                    break;
            }

            // Store booking data template for creating future bookings
            $bookingAmount = $calculatedCosts['total']['total'] ?? 0;
            
            $bookingData = [
                'service_price' => $calculatedCosts['total']['service_price'] ?? 0,
                'subtotal' => $calculatedCosts['total']['subtotal'] ?? 0,
                'tax' => $calculatedCosts['total']['tax'] ?? 0,
                'total' => $bookingAmount,
                'provider_id' => $request->provider_id,
                'service_id' => $request->service_id,
                'address_id' => $request->address_id,
                'date_time' => $firstBookingDateTime->format('Y-m-d H:i:s'),
                'description' => $request->description ?? '',
                'required_servicemen' => $request->required_servicemen ?? 1,
                'type' => $request->type ?? 'fixed',
                // Preserve time component for future bookings
                'time' => $firstBookingDateTime->format('H:i:s'),
            ];

            // Create recurring booking record
            $recurringBooking = RecurringBooking::create([
                'consumer_id' => $userId,
                'provider_id' => $request->provider_id,
                'service_id' => $request->service_id,
                'address_id' => $request->address_id,
                'frequency' => $frequency,
                'start_date' => $startDate->toDateString(),
                'end_date' => $request->end_date ? Carbon::parse($request->end_date)->toDateString() : null,
                'total_occurrences' => $request->total_occurrences ?? null,
                'occurrences_completed' => 0, // Will increment when bookings are created
                'next_booking_date' => $nextBookingDate ? $nextBookingDate->toDateString() : null,
                'payment_method' => 'cash',
                'payment_status' => 'PENDING',
                'amount' => $bookingAmount,
                'currency' => Helpers::getDefaultCurrencyCode(),
                'is_active' => true,
                'status' => 'active',
                'booking_data' => $bookingData,
            ]);

            DB::commit();

            return $recurringBooking;

        } catch (Exception $e) {
            DB::rollBack();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Get user's recurring bookings
     */
    public function getUserRecurringBookings($userId)
    {
        return RecurringBooking::where('consumer_id', $userId)
            ->with(['booking', 'service', 'provider', 'address', 'generatedBookings'])
            ->latest()
            ->get();
    }

    /**
     * Cancel a recurring booking
     */
    public function cancelRecurringBooking($recurringBookingId, $userId)
    {
        $recurringBooking = RecurringBooking::where('id', $recurringBookingId)
            ->where('consumer_id', $userId)
            ->firstOrFail();

        // Cancel subscription in payment gateway
        // This would require implementing cancel methods in payment classes
        // For now, just mark as cancelled in database
        
        $recurringBooking->cancel();

        return $recurringBooking;
    }
}
