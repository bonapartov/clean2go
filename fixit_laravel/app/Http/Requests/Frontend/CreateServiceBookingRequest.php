<?php

namespace App\Http\Requests\Frontend;

use App\Enums\ServiceTypeEnum;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class CreateServiceBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $serviceId = $this->input('service_id');
        $isScheduledBooking = $this->input('is_scheduled_booking') == 1;
        
        // Check if service type is scheduled
        if ($serviceId) {
            $service = Service::find($serviceId);
            $isScheduledService = $service && $service->type === ServiceTypeEnum::SCHEDULED;
        } else {
            $isScheduledService = false;
        }

        $rules = [
            'address_id' => 'required',
            'service_id' => 'required|exists:services,id',
            'select_serviceman' => 'nullable',
        ];

        // For scheduled services, validate scheduled booking fields
        if ($isScheduledBooking || $isScheduledService) {
            $rules['is_scheduled_booking'] = 'required|in:1';
            $rules['select_date_time'] = 'nullable';
            $rules['date_time'] = 'nullable';
            $rules['schedule_start_date'] = 'required|date';
            $rules['schedule_end_date'] = 'required|date|after_or_equal:schedule_start_date';
            $rules['schedule_time'] = 'required';
            $rules['booking_frequency'] = 'required|in:daily,weekly,monthly,yearly,custom';
            $rules['scheduled_dates_json'] = 'required|json';
            $rules['scheduled_services_count'] = 'required|integer|min:1';
            
            // Conditional validation for weekdays and custom dates
            $rules['selected_weekdays'] = 'nullable|array';
            $rules['selected_weekdays.*'] = 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday';
            $rules['custom_dates'] = 'nullable|string';
        } else {
            // For regular services, validate date_time
            $rules['select_date_time'] = 'required|in:custom,timeslot';
            $rules['date_time'] = 'required_if:select_date_time,custom';
        }

        return $rules;
    }
}
