<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\RecurringBooking;
use App\Enums\BookingEnumSlug;
use App\Helpers\Helpers;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateRecurringBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:generate-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate booking records for recurring subscriptions that are due';

    /**
     * Execute the console command.
     * Creates bookings 1 day before the scheduled date
     */
    public function handle()
    {
        $this->info('Starting recurring booking generation (1 day before scheduled date)...');

        // Get active recurring bookings where next_booking_date is tomorrow
        // This means we create booking today for tomorrow's service
        $tomorrow = Carbon::tomorrow()->toDateString();
        
        $recurringBookings = RecurringBooking::active()
            ->where('next_booking_date', $tomorrow)
            ->get();

        $this->info("Found {$recurringBookings->count()} recurring bookings to process for tomorrow ({$tomorrow}).");

        $generated = 0;
        $failed = 0;

        foreach ($recurringBookings as $recurringBooking) {
            try {
                DB::beginTransaction();

                // Check if we should continue generating bookings
                if (!$recurringBooking->shouldContinue()) {
                    $this->warn("Skipping recurring booking {$recurringBooking->id} - conditions not met");
                    DB::rollBack();
                    continue;
                }

                // Check if booking already exists for this date
                $existingBooking = Booking::where('recurring_booking_id', $recurringBooking->id)
                    ->whereDate('date_time', $tomorrow)
                    ->first();
                
                if ($existingBooking) {
                    $this->info("Booking already exists for recurring booking {$recurringBooking->id} on {$tomorrow}");
                    DB::rollBack();
                    continue;
                }

                // Get booking data template
                $bookingData = $recurringBooking->booking_data ?? [];
                
                if (empty($bookingData)) {
                    throw new \Exception("No booking data template found for recurring booking {$recurringBooking->id}");
                }

                // Generate booking number
                $bookingNumber = (string) $this->generateBookingNumber(6);
                $bookingStatusId = Helpers::getbookingStatusIdBySlug(BookingEnumSlug::PENDING);

                // Calculate date_time for the new booking (tomorrow with preserved time)
                $bookingDateTime = Carbon::parse($tomorrow);
                if (isset($bookingData['time'])) {
                    // Use time from booking_data template
                    $timeParts = explode(':', $bookingData['time']);
                    $bookingDateTime->setTime($timeParts[0] ?? 17, $timeParts[1] ?? 0, $timeParts[2] ?? 0);
                } elseif (isset($bookingData['date_time'])) {
                    // Extract time from original date_time
                    $originalDateTime = Carbon::parse($bookingData['date_time']);
                    $bookingDateTime->setTime($originalDateTime->hour, $originalDateTime->minute);
                }

                // Create new booking using BookingTrait method
                $bookingRequest = new \Illuminate\Http\Request();
                $bookingRequest->merge([
                    'consumer_id' => $recurringBooking->consumer_id,
                    'service_id' => $recurringBooking->service_id,
                    'provider_id' => $recurringBooking->provider_id,
                    'address_id' => $recurringBooking->address_id,
                    'date_time' => $bookingDateTime->format('Y-m-d H:i:s'),
                    'type' => $bookingData['type'] ?? 'fixed',
                    'payment_method' => 'cash',
                    'required_servicemen' => $bookingData['required_servicemen'] ?? 1,
                    'description' => $bookingData['description'] ?? "Recurring service booking #{$recurringBooking->id}",
                ]);

                // Use booking repository to create booking properly
                $bookingRepository = app(\App\Repositories\API\BookingRepository::class);
                $newBooking = $bookingRepository->placeBooking($bookingRequest);
                
                // Set recurring booking flags
                $newBooking->recurring_booking_id = $recurringBooking->id;
                $newBooking->is_recurring = true;
                $newBooking->payment_method = 'cash';
                $newBooking->payment_status = \App\Enums\PaymentStatus::PENDING;
                $newBooking->save();

                // Update recurring booking - calculate next booking date
                $recurringBooking->increment('occurrences_completed');
                
                // Calculate next booking date from the booking date (tomorrow), not today
                $nextDate = $recurringBooking->calculateNextBookingDate($bookingDateTime);
                $recurringBooking->next_booking_date = $nextDate ? $nextDate->toDateString() : null;
                
                // Check if we should stop
                if (!$recurringBooking->shouldContinue()) {
                    $recurringBooking->status = 'completed';
                    $recurringBooking->is_active = false;
                    $recurringBooking->next_booking_date = null;
                }
                
                $recurringBooking->save();

                DB::commit();
                $generated++;

                $this->info("Generated booking #{$newBooking->booking_number} for recurring booking {$recurringBooking->id} scheduled for {$tomorrow}");

                Log::info("Generated recurring booking", [
                    'recurring_booking_id' => $recurringBooking->id,
                    'booking_id' => $newBooking->id,
                    'booking_number' => $newBooking->booking_number,
                    'scheduled_date' => $tomorrow,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;

                $this->error("Failed to generate booking for recurring booking {$recurringBooking->id}: {$e->getMessage()}");
                
                Log::error("Failed to generate recurring booking", [
                    'recurring_booking_id' => $recurringBooking->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info("Completed: {$generated} bookings generated for tomorrow, {$failed} failed.");

        return Command::SUCCESS;
    }

    /**
     * Generate unique booking number
     */
    private function generateBookingNumber($digits)
    {
        $i = 0;
        do {
            $bookingNumber = pow(8, $digits) + $i++;
        } while (Booking::where('booking_number', '=', $bookingNumber)->first());

        return $bookingNumber;
    }
}
