<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Traits\PaymentTrait;
use App\Models\RecurringBooking;
use App\Repositories\API\BookingRepository;
use App\Repositories\API\RecurringBookingRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RecurringBookingController extends Controller
{
    use PaymentTrait;

    protected $repository;
    protected $bookingRepository;

    public function __construct(RecurringBookingRepository $repository, BookingRepository $bookingRepository)
    {
        $this->repository = $repository;
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * Create a recurring booking record
     * Booking will be auto-generated 1 day before scheduled date via cron job
     * POST /api/recurring-booking
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate request
            $request->validate([
                'frequency' => 'required|in:weekly,monthly,yearly',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'nullable|date|after:start_date',
                'total_occurrences' => 'nullable|integer|min:1',
                // Booking data fields (same as regular booking)
                'service_id' => 'required|exists:services,id',
                'provider_id' => 'nullable|exists:users,id',
                'address_id' => 'required|exists:addresses,id',
                'date_time' => 'required|date', // First booking date/time
            ]);

            // Calculate booking costs to store in booking_data
            $bookingRequest = $request->merge([
                'type' => 'booking',
                'payment_method' => 'cash',
            ]);

            $calculatedCosts = $this->bookingRepository->calculate($bookingRequest);
            
            // Create recurring booking record (no individual bookings yet)
            $recurringBooking = $this->repository->createRecurringBooking($request, $calculatedCosts);

            return response()->json([
                'success' => true,
                'message' => 'Recurring service booking created successfully. Bookings will be automatically created 1 day before each scheduled date.',
                'data' => [
                    'recurring_booking' => $recurringBooking,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Get user's recurring bookings
     * GET /api/recurring-booking
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = Helpers::getCurrentUserId();
            $recurringBookings = $this->repository->getUserRecurringBookings($userId);

            return response()->json([
                'success' => true,
                'data' => $recurringBookings,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific recurring booking
     * GET /api/recurring-booking/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $recurringBooking = RecurringBooking::with(['booking', 'service', 'provider', 'address', 'generatedBookings'])
                ->where('consumer_id', Helpers::getCurrentUserId())
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $recurringBooking,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recurring booking not found',
            ], 404);
        }
    }

    /**
     * Cancel recurring booking
     * DELETE /api/recurring-booking/{id}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $recurringBooking = $this->repository->cancelRecurringBooking($id, Helpers::getCurrentUserId());

            return response()->json([
                'success' => true,
                'message' => 'Recurring booking cancelled successfully',
                'data' => $recurringBooking,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Pause recurring booking
     * POST /api/recurring-booking/{id}/pause
     */
    public function pause($id): JsonResponse
    {
        try {
            $recurringBooking = RecurringBooking::where('id', $id)
                ->where('consumer_id', Helpers::getCurrentUserId())
                ->firstOrFail();

            $recurringBooking->pause();

            return response()->json([
                'success' => true,
                'message' => 'Recurring booking paused successfully',
                'data' => $recurringBooking,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Resume recurring booking
     * POST /api/recurring-booking/{id}/resume
     */
    public function resume($id): JsonResponse
    {
        try {
            $recurringBooking = RecurringBooking::where('id', $id)
                ->where('consumer_id', Helpers::getCurrentUserId())
                ->firstOrFail();

            $recurringBooking->resume();

            return response()->json([
                'success' => true,
                'message' => 'Recurring booking resumed successfully',
                'data' => $recurringBooking,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
