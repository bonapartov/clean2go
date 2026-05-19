<?php

namespace App\Http\Requests\API;

use App\Enums\ServiceTypeEnum;
use App\Exceptions\ExceptionHandler;
use App\Models\Service;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CalculateCheckoutRequest extends FormRequest
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
        $rules = [
            'consumer_id' => ['exists:users,id'],
            'services' => ['array'],
            'services.*.service_id' => ['required', 'exists:services,id'],
            'coupon' => ['nullable', 'exists:coupons,code'],
            'payment_method' => ['required'],
            'services.*.additional_services' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    foreach ($this->services as $service) {
                        $mainService = Service::find($service['service_id']);
                        if ($mainService && !empty($service['additional_services'])) {
                            $requestedIds = collect($service['additional_services'])->pluck('id')->toArray();
                            $validIds = $mainService->additionalServices->pluck('id')->toArray();
                            $invalidAdditionalServices = array_diff($requestedIds, $validIds);
                            if (count($invalidAdditionalServices) > 0) {
                                foreach ($invalidAdditionalServices as $invalidServiceId) {
                                    $fail(__('static.additional_service_invalid_with_id', ['id' => $invalidServiceId]));
                                }
                            }
                        }
                    }
                }
            ],
        ];

        // Add conditional validation for date_time and scheduled booking fields
        foreach ($this->services ?? [] as $index => $service) {
            // Check if is_scheduled_booking flag is set (handle 1, true, '1')
            $isScheduledBooking = isset($service['is_scheduled_booking']) && 
                ($service['is_scheduled_booking'] == 1 || $service['is_scheduled_booking'] === true || $service['is_scheduled_booking'] === '1');
            
            // Check if service type is scheduled (fallback check)
            $serviceModel = null;
            $isScheduledService = false;
            if (isset($service['service_id'])) {
                $serviceModel = Service::find($service['service_id']);
                $isScheduledService = $serviceModel && $serviceModel->type === ServiceTypeEnum::SCHEDULED;
            }
            
            if ($isScheduledBooking || $isScheduledService) {
                // For scheduled bookings, validate scheduled fields
                $rules["services.{$index}.is_scheduled_booking"] = 'required|in:1,true,"1"';
                $rules["services.{$index}.schedule_start_date"] = 'required|date';
                $rules["services.{$index}.schedule_end_date"] = 'required|date|after_or_equal:services.' . $index . '.schedule_start_date';
                $rules["services.{$index}.schedule_time"] = 'required';
                $rules["services.{$index}.booking_frequency"] = 'required|in:daily,weekly,monthly,yearly,custom';
                $rules["services.{$index}.scheduled_dates_json"] = 'required|array|min:1';
                $rules["services.{$index}.scheduled_services_count"] = 'required|integer|min:1';
                $rules["services.{$index}.selected_weekdays"] = 'nullable|array';
                $rules["services.{$index}.selected_weekdays.*"] = 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday';
            }
            
            // date_time validation - conditional based on scheduled booking
            // Use nullable first, then add conditional required check
            $rules["services.{$index}.date_time"] = [
                'nullable', // Make it nullable first, then check conditionally
                function ($attribute, $value, $fail) use ($index) {
                    // Re-check at validation time in case data changed
                    $service = $this->services[$index] ?? [];
                    $isScheduled = isset($service['is_scheduled_booking']) && 
                        ($service['is_scheduled_booking'] == 1 || $service['is_scheduled_booking'] === true || $service['is_scheduled_booking'] === '1');
                    
                    // Also check service type
                    if (!$isScheduled && isset($service['service_id'])) {
                        $serviceModel = Service::find($service['service_id']);
                        $isScheduled = $serviceModel && $serviceModel->type === ServiceTypeEnum::SCHEDULED;
                    }
                    
                    // If scheduled booking, date_time is optional - skip validation
                    if ($isScheduled) {
                        return; // Skip validation for scheduled bookings
                    }
                    
                    // For regular bookings, date_time is required
                    if (empty($value) || $value === null) {
                        $fail("The $attribute field is required for regular bookings.");
                        return;
                    }
                    
                    // Validate date format for regular bookings
                    try {
                        $dateTime = \Carbon\Carbon::createFromFormat('d-M-Y,h:i a', $value);
                        $now = now();
                        if ($dateTime->isSameDay($now)) {
                            if ($dateTime->lt($now->copy()->addHour())) {
                                $fail("The $attribute must be at least 1 hour from now.");
                            }
                        }
                        
                    } catch (\Exception $e) {
                        $fail("The $attribute must be a valid date and time in format 'DD-MMM-YYYY,HH:mm am/pm'.");
                    }
                }
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'services.*.service_id.exists' => __('static.service_id_invalid'),
            'coupon.exists' => __('static.coupon_code_not_found', ['code' => $this->coupon]),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ExceptionHandler($validator->errors()->first(), 422);
    }
}
