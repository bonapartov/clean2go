<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
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
        return [
            'title' => 'required|max:255',
            'category_id' => 'array|required',
            'required_servicemen' => 'required|numeric',
            'user_id' => 'nullable|exists:users,id',
            'price' => 'required_if:type,fixed,provider_site,remotely,scheduled',
            'type' => 'required|in:fixed,provider_site,remotely,scheduled',
            'service_rate' => 'required',
            'duration' => 'required',
            'duration_unit' => 'required',
            'service_id' => 'array|nullable',
            'per_serviceman_commission' => 'required|numeric|between:0,100',
            'is_advance_payment_enabled' => 'boolean',
            'advance_payment_percentage' => 'required_if:is_advance_payment_enabled,1|exclude_if:type,scheduled|nullable|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'The Provider field is required',
            'type' => 'Please select a service type',
            'price.required_if' => 'The price field is required',
        ];
    }
}
