<?php

namespace App\Http\Requests\API;

use App\Enums\ServiceTypeEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\Service;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $roleName = Helpers::getCurrentRoleName();
        $booking = [
            'services' => ['array'],
            'consumer_id' => 'exists:users,id,deleted_at,NULL',
            'services.*.service_id' => ['exists:services,id,deleted_at,NULL'],
            'coupon' => ['nullable', 'exists:coupons,code,deleted_at,NULL'],
            'services.*.select_serviceman' => 'in:as_per_my_choice,app_choose',
            'payment_method' => ['string'],
        ];

        // Add conditional validation for scheduled booking fields
        foreach ($this->services ?? [] as $index => $service) {
            $isScheduledBooking = isset($service['is_scheduled_booking']) && $service['is_scheduled_booking'] == 1;
            
            // Check if service type is scheduled
            $serviceModel = Service::find($service['service_id'] ?? null);
            $isScheduledService = $serviceModel && $serviceModel->type === ServiceTypeEnum::SCHEDULED;
            
            if ($isScheduledBooking || $isScheduledService) {
                // For scheduled bookings, validate scheduled fields
                $booking["services.{$index}.is_scheduled_booking"] = 'required|in:1';
                $booking["services.{$index}.schedule_start_date"] = 'required|date';
                $booking["services.{$index}.schedule_end_date"] = 'required|date|after_or_equal:services.' . $index . '.schedule_start_date';
                $booking["services.{$index}.schedule_time"] = 'required';
                $booking["services.{$index}.booking_frequency"] = 'required|in:daily,weekly,monthly,yearly,custom';
                $booking["services.{$index}.scheduled_dates_json"] = 'required|array|min:1';
                $booking["services.{$index}.scheduled_services_count"] = 'required|integer|min:1';
                $booking["services.{$index}.selected_weekdays"] = 'nullable|array';
                $booking["services.{$index}.selected_weekdays.*"] = 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday';
                $booking["services.{$index}.date_time"] = 'nullable'; // Not required for scheduled
            } else {
                // For regular bookings, date_time validation can be added if needed
                // Currently not required in CreateBookingRequest, but can be added
            }
        }

        return $booking;
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
