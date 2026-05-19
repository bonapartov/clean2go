<?php

namespace App\Http\Traits;

use App\Enums\BookingEnum;
use App\Enums\BookingEnumSlug;
use App\Enums\PaymentStatus;
use App\Enums\RoleEnum;
use App\Events\CreateBookingEvent;
use App\Events\UpdateBookingStatusEvent;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\Booking;
use App\Models\BookingReasonLog;
use App\Models\BookingStatusLog;
use App\Models\Service;
use App\Models\VideoConsultation;
use Carbon\Carbon;
use Exception;
use Jubaer\Zoom\Facades\Zoom;
use Illuminate\Support\Str;

trait BookingTrait
{
    use CheckoutTrait, PaymentTrait, TransactionsTrait;

    public function getBookingNumber($digits)
    {
        $i = 0;
        do {
            $booking_number = pow(8, $digits) + $i++;
        } while (Booking::where('booking_number', '=', $booking_number)->first());

        return $booking_number;
    }

    public function placeBooking($request)
    {
        try {
            $items = $this->calculate($request);
            
            // Check if any service is a scheduled booking
            $hasScheduledBooking = false;
            if (isset($items['services']) && is_array($items['services'])) {
                foreach ($items['services'] as $service) {
                    $isScheduled = isset($service['is_scheduled_booking']) && 
                        ($service['is_scheduled_booking'] == true || $service['is_scheduled_booking'] == 1 || $service['is_scheduled_booking'] === '1');
                    if ($isScheduled) {
                        $hasScheduledBooking = true;
                        break;
                    }
                }
            }
            
            // For scheduled bookings, skip the initial booking call - storeService will create parent and children
            // For regular bookings, create parent booking first
            if ($hasScheduledBooking) {
                $booking = $this->storeBooking($items, $request, null);
                // For scheduled bookings, storeBooking returns the parent booking
                return $booking;
            } else {
                // Regular booking flow
                $booking = $this->booking($items, $request);
                $this->storeBooking($items, $request, $booking);
                return $booking;
            }

        } catch (Exception $e) {

            throw new ExceptionHandler($e?->getMessage(), $e->getCode());
        }
    }

    public function booking($service, $request)
    {
        $booking_number = (string) $this->getBookingNumber(6);
        $bookingStatusId =  Helpers::getbookingStatusIdBySlug(BookingEnumSlug::PENDING);
        if(isset($booking->parent_id)){
            $booking->provider_id == null;
        }

        // Calculate advance payment amounts
        $totalAmount = $service['total']['total'];
        $advancePaymentAmount = $totalAmount;
        $remainingPaymentAmount = 0;
        $isAdvanceEnabled = false;
        $advancePercentage = null;

        // First, check if advance payment info is already calculated in the total (from calculateCosts)
        if (isset($service['total']['is_advance_payment_enabled']) && $service['total']['is_advance_payment_enabled']) {
            $isAdvanceEnabled = true;
            $advancePercentage = $service['total']['advance_payment_percentage'] ?? null;
            $advancePaymentAmount = $service['total']['advance_payment_amount'] ?? $totalAmount;
            $remainingPaymentAmount = $service['total']['remaining_payment_amount'] ?? 0;
        } else {
            // Fallback: Check individual services for advance payment
            // Handle case where $service is the full items array
            $firstServiceId = null;
            
            if (isset($service['service_id']) && $service['service_id']) {
                // Single service structure
                $firstServiceId = $service['service_id'];
            } elseif (isset($service['services']) && is_array($service['services']) && count($service['services']) > 0) {
                // Full items array with services
                $firstServiceId = $service['services'][0]['service_id'] ?? null;
            } elseif (isset($service['services_package']) && is_array($service['services_package']) && count($service['services_package']) > 0) {
                // Full items array with service packages
                $firstPackage = $service['services_package'][0];
                if (isset($firstPackage['services']) && is_array($firstPackage['services']) && count($firstPackage['services']) > 0) {
                    $firstServiceId = $firstPackage['services'][0]['service_id'] ?? null;
                }
            }
            
            // Check if service has advance payment enabled
            if ($firstServiceId) {
                $serviceModel = Service::find($firstServiceId);
                if ($serviceModel && $serviceModel->is_advance_payment_enabled && $serviceModel->advance_payment_percentage > 0) {
                    $isAdvanceEnabled = true;
                    $advancePercentage = $serviceModel->advance_payment_percentage;
                    $advancePaymentAmount = round(($totalAmount * $advancePercentage) / 100, 2);
                    $remainingPaymentAmount = round($totalAmount - $advancePaymentAmount, 2);
                }
            }
        }

        $booking = Booking::create([
            'booking_number' => $booking_number,
            'consumer_id' => $request->consumer_id ?? auth()->user()->id,
            'coupon_id' => $service['coupon_id'] ?? null,
            'provider_id' => $service['provider_id'] ?? null,
            'service_id' => $service['service_id'] ?? null,
            'service_package_id' => $service['service_package_id'] ?? null,
            'address_id' => $service['address_id'] ?? null,
            'service_price' => $service['service_price'] ?? null,
            'tax' => $service['total']['tax'],
            'description' => $service['description'] ?? null,
            'per_serviceman_charge' => $service['per_serviceman_charge'] ?? null,
            'required_servicemen' => $service['total']['required_servicemen'] ?? null,
            'total_extra_servicemen' => $service['total']['total_extra_servicemen'],
            'total_servicemen' => $service['total']['total_servicemen'] ?? null,
            'requircreateBookingervicemen' => $service['total']['total_servicemen'] ?? null,
            'total_extra_servicemen_charge' => $service['total']['total_serviceman_charge'],
            'coupon_total_discount' => $service['total']['coupon_total_discount'] ?? null,
            'platform_fees' => $service['total']['platform_fees'] ?? null,
            'platform_fees_type' => $service['total']['platform_fees_type'] ?? null,
            'subtotal' => $service['total']['subtotal'],
            'total' => $totalAmount,
            'booking_status_id' => $bookingStatusId,
            'parent_id' => $request->parent_id,
            'date_time' => $this->dateTimeFormater($service['date_time'] ?? null),
            'payment_method' => $request->payment_method,
            'invoice_url' => $this->generateInvoiceUrl($booking_number),
            'created_by_id' => Helpers::getCurrentUserId(),
            'advance_payment_amount' => $advancePaymentAmount,
            'remaining_payment_amount' => $remainingPaymentAmount,
            'advance_payment_status' => 'PENDING',
            'remaining_payment_status' => 'PENDING',
            'is_advance_payment_enabled' => $isAdvanceEnabled,
            'advance_payment_percentage' => $advancePercentage,
            'transaction_ids' => [],
        ]);
        
        $ids = array_filter($service['servicemen_ids'] ?? [], fn($id) => !empty($id));
        if (!empty($ids)) {
            $booking->servicemen()->attach($ids);
            $booking->servicemen;
        }

        if (!empty($service['taxes']) && is_array($service['taxes'])) {
            foreach ($service['taxes'] as $tax) {
                $booking->taxes()->attach($tax['id'], [
                    'rate' => $tax['rate'],
                    'amount' => $tax['amount']
                ]);
            }
        }

        $logData = [
            'title' => 'Pending booking request',
            'description' => 'New booking is added.',
            'booking_id' => $booking->id,
            'booking_status_id' => $bookingStatusId,
        ];
        BookingStatusLog::create($logData);

        if (isset($service['additional_services'])) {
            foreach ($service['additional_services'] as $additionalService) {
                $booking->additional_services()->attach($additionalService['id'], [
                    'price' => $additionalService['price'],
                    'qty' => $additionalService['qty'],
                    'total_price' => $additionalService['total_price'],
                ]);
            }
        }

        if(isset($booking->parent_id)){
            event(new CreateBookingEvent($booking));
        }
        return $booking;
    }

//  /**
//      * Create a Zoom meeting for a booking
//      */
//     public function createZoomMeeting(Booking $booking)
//     {
//         try {
//             $meeting = Zoom::user()->find('me')->meetings()->create([
//                 'topic'      => $booking->service->name ?? 'Consultation',
//                 'agenda'     => $booking->description ?? 'Video Consultation',
//                 'type'       => 2,
//                 'start_time' => Carbon::parse($booking->date_time)->toIso8601String(),
//                 'duration'   => 30,
//                 'timezone'   => config('app.timezone'),
//                 'password'   => Str::random(8),
//                 'settings'   => [
//                     'join_before_host'  => true,
//                     'host_video'        => true,
//                     'participant_video' => true,
//                     'mute_upon_entry'   => true,
//                     'waiting_room'      => false,
//                 ],
//             ]);

//             VideoConsultation::create([
//                 'meeting_type'  => 'zoom',
//                 'agenda'        => $booking->description ?? 'Video Consultation',
//                 'topic'         => $booking->service->name ?? 'Consultation',
//                 'type'          => 2,
//                 'duration'      => 30,
//                 'timezone'      => config('app.timezone'),
//                 'password'      => $meeting->password ?? null,
//                 'start_time'    => Carbon::parse($booking->date_time),
//                 'settings'      => json_encode($meeting->settings ?? []),
//                 'join_url'      => $meeting->join_url ?? null,
//                 'start_url'     => $meeting->start_url ?? null,
//                 'created_by_id' => $booking->created_by_id,
//             ]);

//         } catch (Exception $e) {
//             throw new Exception('Zoom meeting creation failed: ' . $e->getMessage());
//         }
//     }

    public function dateTimeFormater($dateTime)
    {
        try {
            if (is_null($dateTime)) {
                return null;
            }

            if (preg_match('/^\d{1,2}-[A-Za-z]{3}-\d{4}, \d{1,2}:\d{2} [ap]m$/', $dateTime)) {
                return Carbon::createFromFormat('j-M-Y, g:i a', $dateTime)->format('Y-m-d H:i:s');
            }

            // If already in `Y-m-d H:i:s` format, return as is
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $dateTime)) {
                return $dateTime;
            }

            // If it's in `Y-m-d H:i` format, append ":00" for seconds
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $dateTime)) {
                return $dateTime . ':00';
            }

            // return Carbon::createFromFormat('j-M-Y, g:i a', trim($dateTime))?->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function storeBooking($item, $request, $parentBooking = null)
    {
        $request->merge(['parent_id' => $parentBooking?->id]);
        if (isset($item['services_package'])) {
            foreach ($item['services_package'] as $service_package) {
                $this->storeService($service_package['services'], $request);
            }
        }

        return $this->storeService($item['services'], $request);
    }

    public function storeService($services, $request)
    {
        $booking = null;
        foreach ($services as $service) {
            // Check if this is a scheduled booking (handle both boolean and integer)
            $isScheduledService = isset($service['is_scheduled_booking']) && 
                ($service['is_scheduled_booking'] == true || $service['is_scheduled_booking'] == 1 || $service['is_scheduled_booking'] === '1');
            
            if ($isScheduledService) {
                // Create parent booking first with grand total
                $parentBooking = $this->createScheduledParentBooking($service, $request);
                
                // Create child bookings for each scheduled date/time
                if (isset($service['scheduled_dates_json']) && is_array($service['scheduled_dates_json'])) {
                    foreach ($service['scheduled_dates_json'] as $scheduledDate) {
                        $this->createScheduledChildBooking($service, $request, $parentBooking, $scheduledDate);
                    }
                }
                
                $booking = $parentBooking;
            } else {
                // Regular booking
                $booking = $this->booking($service, $request);
            }
        }

        return $booking;
    }
    
    /**
     * Create parent booking for scheduled service with grand total
     */
    protected function createScheduledParentBooking($service, $request)
    {
        $booking_number = (string) $this->getBookingNumber(6);
        $bookingStatusId = Helpers::getbookingStatusIdBySlug(BookingEnumSlug::PENDING);
        
        // Get the base service price (for one instance)
        $baseServicePrice = $service['service_price'] ?? 0;
        $baseSubtotal = $service['base_subtotal'] ?? $service['total']['subtotal'] ?? 0;
        $baseTax = $service['base_tax'] ?? $service['total']['tax'] ?? 0;
        $basePlatformFees = $service['base_platform_fees'] ?? $service['total']['platform_fees'] ?? 0;
        $baseTotal = $service['base_total'] ?? $service['total']['total'] ?? 0;
        
        // Calculate grand total (base total * scheduled services count)
        $scheduledCount = $service['scheduled_services_count'] ?? 1;
        $grandTotal = $baseTotal * $scheduledCount;
        $grandSubtotal = $baseSubtotal * $scheduledCount;
        $grandTax = $baseTax * $scheduledCount;
        $grandPlatformFees = $basePlatformFees * $scheduledCount;
        
        // Calculate advance payment for grand total
        $advancePaymentAmount = $grandTotal;
        $remainingPaymentAmount = 0;
        $isAdvanceEnabled = false;
        $advancePercentage = null;
        
        if (isset($service['total']['is_advance_payment_enabled']) && $service['total']['is_advance_payment_enabled']) {
            $isAdvanceEnabled = true;
            $advancePercentage = $service['total']['advance_payment_percentage'] ?? null;
            $advancePaymentAmount = round(($grandTotal * $advancePercentage) / 100, 2);
            $remainingPaymentAmount = round($grandTotal - $advancePaymentAmount, 2);
        }
        
        // Parse scheduled dates JSON if it's a string
        $scheduledDatesJson = $service['scheduled_dates_json'];
        if (is_string($scheduledDatesJson)) {
            $scheduledDatesJson = json_decode($scheduledDatesJson, true);
        }
        
        // Parse selected weekdays if it's a string
        $selectedWeekdays = $service['selected_weekdays'] ?? null;
        if (is_string($selectedWeekdays)) {
            $selectedWeekdays = json_decode($selectedWeekdays, true);
        }
        
        // Get coupon_id from request if available
        $couponId = null;
        if (isset($request->coupon)) {
            if (is_string($request->coupon)) {
                $coupon = Helpers::getCoupon($request->coupon);
                $couponId = $coupon->id ?? null;
            } elseif (is_object($request->coupon) && isset($request->coupon->id)) {
                $couponId = $request->coupon->id;
            }
        }
        // Fallback to service coupon_id if available
        if (!$couponId && isset($service['coupon_id'])) {
            $couponId = $service['coupon_id'];
        }
        
        $parentBooking = Booking::create([
            'booking_number' => $booking_number,
            'consumer_id' => $request->consumer_id ?? auth()->user()->id,
            'coupon_id' => $couponId,
            'provider_id' => $service['provider_id'] ?? null,
            'service_id' => $service['service_id'] ?? null,
            'service_package_id' => $service['service_package_id'] ?? null,
            'address_id' => $service['address_id'] ?? null,
            'service_price' => $baseServicePrice, // Base price for one instance
            'tax' => $grandTax,
            'description' => $service['description'] ?? null,
            'per_serviceman_charge' => $service['per_serviceman_charge'] ?? null,
            'required_servicemen' => $service['total']['required_servicemen'] ?? null,
            'total_extra_servicemen' => ($service['total']['total_extra_servicemen'] ?? 0) * $scheduledCount,
            'total_servicemen' => $service['total']['total_servicemen'] ?? null,
            'total_extra_servicemen_charge' => ($service['total']['total_serviceman_charge'] ?? 0) * $scheduledCount,
            'coupon_total_discount' => ($service['total']['coupon_total_discount'] ?? 0) * $scheduledCount,
            'platform_fees' => $grandPlatformFees,
            'platform_fees_type' => $service['total']['platform_fees_type'] ?? null,
            'subtotal' => $grandSubtotal,
            'total' => $grandTotal, // Grand total for all scheduled services
            'booking_status_id' => $bookingStatusId,
            'parent_id' => null, // Parent booking has no parent
            'date_time' => null, // Parent booking doesn't have a specific date/time
            'payment_method' => $request->payment_method,
            'invoice_url' => $this->generateInvoiceUrl($booking_number),
            'created_by_id' => Helpers::getCurrentUserId(),
            'advance_payment_amount' => $advancePaymentAmount,
            'remaining_payment_amount' => $remainingPaymentAmount,
            'advance_payment_status' => 'PENDING',
            'remaining_payment_status' => 'PENDING',
            'is_advance_payment_enabled' => $isAdvanceEnabled,
            'advance_payment_percentage' => $advancePercentage,
            'transaction_ids' => [],
            // Scheduled booking fields
            'is_scheduled_booking' => true,
            'booking_frequency' => $service['booking_frequency'] ?? null,
            'schedule_start_date' => $service['schedule_start_date'] ?? null,
            'schedule_end_date' => $service['schedule_end_date'] ?? null,
            'schedule_time' => $service['schedule_time'] ?? null,
            'selected_weekdays' => $selectedWeekdays,
            'scheduled_dates_json' => $scheduledDatesJson,
            'scheduled_services_count' => $scheduledCount,
        ]);
        
        // Attach servicemen to parent booking
        $ids = array_filter($service['servicemen_ids'] ?? [], fn($id) => !empty($id));
        if (!empty($ids)) {
            $parentBooking->servicemen()->attach($ids);
        }
        
        // Attach additional services to parent booking (multiplied by count)
        if (isset($service['additional_services']) && is_array($service['additional_services'])) {
            foreach ($service['additional_services'] as $additionalService) {
                $parentBooking->additional_services()->attach($additionalService['id'], [
                    'price' => $additionalService['price'],
                    'qty' => ($additionalService['qty'] ?? 1) * $scheduledCount,
                    'total_price' => ($additionalService['total_price'] ?? 0) * $scheduledCount,
                ]);
            }
        }
        
        // Attach taxes
        if (!empty($service['taxes']) && is_array($service['taxes'])) {
            foreach ($service['taxes'] as $tax) {
                $parentBooking->taxes()->attach($tax['id'], [
                    'rate' => $tax['rate'],
                    'amount' => ($tax['amount'] ?? 0) * $scheduledCount
                ]);
            }
        }
        
        // Create status log for parent booking
        $logData = [
            'title' => 'Pending scheduled booking request',
            'description' => 'New scheduled booking is added with ' . $scheduledCount . ' service instances.',
            'booking_id' => $parentBooking->id,
            'booking_status_id' => $bookingStatusId,
        ];
        BookingStatusLog::create($logData);
        
        return $parentBooking;
    }
    
    /**
     * Create child booking for each scheduled date/time instance
     */
    protected function createScheduledChildBooking($service, $request, $parentBooking, $scheduledDate)
    {
        $booking_number = (string) $this->getBookingNumber(6);
        $bookingStatusId = Helpers::getbookingStatusIdBySlug(BookingEnumSlug::PENDING);
        
        // Base amounts for one service instance
        $baseServicePrice = $service['service_price'] ?? 0;
        $baseSubtotal = $service['base_subtotal'] ?? $service['total']['subtotal'] ?? 0;
        $baseTax = $service['base_tax'] ?? $service['total']['tax'] ?? 0;
        $basePlatformFees = $service['base_platform_fees'] ?? $service['total']['platform_fees'] ?? 0;
        $baseTotal = $service['base_total'] ?? $service['total']['total'] ?? 0;
        
        // Calculate advance payment for single instance
        $advancePaymentAmount = $baseTotal;
        $remainingPaymentAmount = 0;
        $isAdvanceEnabled = false;
        $advancePercentage = null;
        
        if (isset($service['total']['is_advance_payment_enabled']) && $service['total']['is_advance_payment_enabled']) {
            $isAdvanceEnabled = true;
            $advancePercentage = $service['total']['advance_payment_percentage'] ?? null;
            $advancePaymentAmount = round(($baseTotal * $advancePercentage) / 100, 2);
            $remainingPaymentAmount = round($baseTotal - $advancePaymentAmount, 2);
        }
        
        // Format date_time from scheduled date
        $dateTime = null;
        if (isset($scheduledDate['date']) && isset($scheduledDate['time'])) {
            // Ensure time format is correct (handle both "12:00" and "12:00:00")
            $time = $scheduledDate['time'];
            if (preg_match('/^\d{2}:\d{2}$/', $time)) {
                // Time is in "HH:mm" format, add seconds
                $time = $time . ':00';
            } elseif (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
                // Time is already in "HH:mm:ss" format
                $time = $time;
            }
            // Construct full datetime: "Y-m-d H:i:s"
            $dateTime = $scheduledDate['date'] . ' ' . $time;
        }
        
        $childBooking = Booking::create([
            'booking_number' => $booking_number,
            'consumer_id' => $request->consumer_id ?? auth()->user()->id,
            'coupon_id' => null, // Coupon applied to parent only
            'provider_id' => $service['provider_id'] ?? null,
            'service_id' => $service['service_id'] ?? null,
            'service_package_id' => $service['service_package_id'] ?? null,
            'address_id' => $service['address_id'] ?? null,
            'service_price' => $baseServicePrice,
            'tax' => $baseTax,
            'description' => $service['description'] ?? null,
            'per_serviceman_charge' => $service['per_serviceman_charge'] ?? null,
            'required_servicemen' => $service['total']['required_servicemen'] ?? null,
            'total_extra_servicemen' => $service['total']['total_extra_servicemen'] ?? 0,
            'total_servicemen' => $service['total']['total_servicemen'] ?? null,
            'total_extra_servicemen_charge' => $service['total']['total_serviceman_charge'] ?? 0,
            'coupon_total_discount' => 0, // Discount applied to parent only
            'platform_fees' => $basePlatformFees,
            'platform_fees_type' => $service['total']['platform_fees_type'] ?? null,
            'subtotal' => $baseSubtotal,
            'total' => $baseTotal, // Base total for one instance
            'booking_status_id' => $bookingStatusId,
            'parent_id' => $parentBooking->id, // Link to parent
            'date_time' => $this->dateTimeFormater($dateTime),
            'payment_method' => $request->payment_method,
            'invoice_url' => $this->generateInvoiceUrl($booking_number),
            'created_by_id' => Helpers::getCurrentUserId(),
            'advance_payment_amount' => $advancePaymentAmount,
            'remaining_payment_amount' => $remainingPaymentAmount,
            'advance_payment_status' => 'PENDING',
            'remaining_payment_status' => 'PENDING',
            'is_advance_payment_enabled' => $isAdvanceEnabled,
            'advance_payment_percentage' => $advancePercentage,
            'transaction_ids' => [],
            // Scheduled booking fields (inherited from parent)
            'is_scheduled_booking' => true,
            'booking_frequency' => $service['booking_frequency'] ?? null,
            'schedule_start_date' => $service['schedule_start_date'] ?? null,
            'schedule_end_date' => $service['schedule_end_date'] ?? null,
            'schedule_time' => $scheduledDate['time'] ?? $service['schedule_time'] ?? null,
            'selected_weekdays' => $service['selected_weekdays'] ?? null,
            'scheduled_dates_json' => [$scheduledDate], // Single date for this child
            'scheduled_services_count' => 1, // Each child is one instance
        ]);
        
        // Attach servicemen to child booking
        $ids = array_filter($service['servicemen_ids'] ?? [], fn($id) => !empty($id));
        if (!empty($ids)) {
            $childBooking->servicemen()->attach($ids);
        }
        
        // Attach additional services to child booking (one instance)
        if (isset($service['additional_services']) && is_array($service['additional_services'])) {
            foreach ($service['additional_services'] as $additionalService) {
                $childBooking->additional_services()->attach($additionalService['id'], [
                    'price' => $additionalService['price'],
                    'qty' => $additionalService['qty'] ?? 1,
                    'total_price' => $additionalService['total_price'] ?? 0,
                ]);
            }
        }
        
        // Attach taxes
        if (!empty($service['taxes']) && is_array($service['taxes'])) {
            foreach ($service['taxes'] as $tax) {
                $childBooking->taxes()->attach($tax['id'], [
                    'rate' => $tax['rate'],
                    'amount' => $tax['amount'] ?? 0
                ]);
            }
        }
        
        // Create status log for child booking
        $logData = [
            'title' => 'Pending scheduled service instance',
            'description' => 'Scheduled service instance for ' . ($dateTime ? Carbon::parse($dateTime)->format('Y-m-d H:i') : 'date/time'),
            'booking_id' => $childBooking->id,
            'booking_status_id' => $bookingStatusId,
        ];
        BookingStatusLog::create($logData);
        
        return $childBooking;
    }

    public function generateInvoiceUrl($booking_number)
    {
        $fullUrl = route('invoice', ['booking_number' => $booking_number]);
        $relativeUrl = str_replace(url('/'), '', $fullUrl);
        return ltrim($relativeUrl, '/');
    }

    // Update Booking Status
    public function updateBookingStatusLogs($request, $booking)
    {
        try {

            if (isset($request['booking_status'])) {
                $booking_status = Helpers::getBookingIdBySlug($request['booking_status']);
                $booking_status_id = $booking_status?->id;
                switch ($booking_status?->name) {
                    case BookingEnum::PENDING:
                        $logData = [
                            'title' => 'Booking is Pending',
                            'description' => 'The booking is in a pending state.',
                        ];
                        break;

                    case BookingEnum::ASSIGNED:
                        $logData = [
                            'title' => 'Booking is Assigned',
                            'description' => 'The booking has been assigned.',
                        ];
                        break;

                    case BookingEnum::ON_THE_WAY:
                        $logData = [
                            'title' => 'Booking is On the Way',
                            'description' => 'The service provider is on the way to the location.',
                        ];
                        break;

                    case BookingEnum::CANCEL:
                        $logData = [
                            'title' => 'Booking Canceled',
                            'description' => 'The booking has been canceled.',
                        ];
                        break;

                    case BookingEnum::ON_HOLD:
                        $logData = [
                            'title' => 'Booking On Hold',
                            'description' => 'The booking is on hold.',
                        ];
                        break;

                    case BookingEnum::START_AGAIN:
                        $logData = [
                            'title' => 'Booking Restarted',
                            'description' => 'The booking has been restarted.',
                        ];
                        break;

                    case BookingEnum::ON_GOING:
                        $logData = [
                            'title' => 'Booking On Going',
                            'description' => 'The booking has been on going.',
                        ];
                        break;

                    case BookingEnum::COMPLETED:
                        $logData = [
                            'title' => 'Booking Completed',
                            'description' => 'The booking has been completed.',
                        ];
                        break;

                    case BookingEnum::ACCEPTED:
                        $roleName = Helpers::getCurrentRoleName();
                        if ($roleName == RoleEnum::PROVIDER) {
                            $logData = [
                                'title' => 'Booking Accepted',
                                'description' => 'The booking has been accepted by the provider.',
                            ];
                        } else {
                            $logData = [
                                'title' => 'Booking Accepted',
                                'description' => 'The booking has been accepted by the serviceman.',
                            ];
                        }
                        break;

                    default:
                        throw new Exception(__('errors.invalid_booking_status'), 422);
                        break;
                }

                $logData['booking_status_id'] = $booking_status_id;
                if ($booking_status?->name == BookingEnum::CANCEL || $booking_status?->name == BookingEnum::ON_HOLD) {
                    if ($booking_status?->name == BookingEnum::CANCEL && !Helpers::canCancelBooking($booking)) {
                        throw new Exception(__('static.booking.cancellation_restricted'), 400);
                    }

                    if ($booking->sub_bookings()) {
                        $booking->sub_bookings()?->update([
                            'booking_status_id' => $booking_status_id,
                        ]);

                        $subBookings = $booking?->sub_bookings()?->get();
                        foreach ($subBookings as $subBooking) {
                            BookingReasonLog::create([
                                'booking_id' => $subBooking->id,
                                'status_id' => $booking_status_id,
                                'reason' => $request['reason'],
                            ]);
                            $logData['booking_id'] = $subBooking->id;
                            $this->bookingStatusLog->create($logData);
                        }

                    } else {
                        BookingReasonLog::create([
                            'booking_id' => $booking->id,
                            'status_id' => $booking_status_id,
                            'reason' => $request['reason'],
                        ]);
                    }
                }

                $logData['booking_id'] = $booking->id;
                BookingStatusLog::create($logData);

                // Refresh booking to avoid relationship data in update
                $booking = $booking->fresh();
                
                // Only update booking_status_id - ensure no relationship data is included
                $booking->booking_status_id = $booking_status_id;
                $booking->save();
                if ($booking_status_id == Helpers::getbookingStatusId(BookingEnum::COMPLETED)) {
                    app(\App\Services\CommissionService::class)->handleCommission($booking);
                }

                // if ($booking->payment_status != PaymentStatus::COMPLETED && $booking->booking_status?->slug == BookingEnumSlug::COMPLETED) {
                //     $this->updatePaymentStatusWithCharge($booking, PaymentStatus::COMPLETED, $booking_status_id);
                //     $booking->save();
                // }

                event(new UpdateBookingStatusEvent($booking));
            }
        } catch (Exception $e) {

            throw new ExceptionHandler($e?->getMessage(), $e->getCode());
        }
    }

    public function updatePaymentStatusWithCharge($booking, $status, $booking_status_id = null)
    {
        $booking->payment_status = $status;
        $booking->sub_bookings()?->update([
            'payment_status' => $status
        ]);
        $booking->extra_charges()?->update([
            'payment_status' => $status
        ]);
        if($booking?->parent){
            $data = ['payment_status' => $status];
            if ($booking_status_id) {
                $data['booking_status_id'] = $booking_status_id;
            }
            $booking->parent->update($data);
        }
    }
}
