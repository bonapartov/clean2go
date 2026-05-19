<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateZoneManagerRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,NULL,id,deleted_at,NULL',
            'phone' => 'required|unique:users|max:255|min:5|unique:users,phone,NULL,id,deleted_at,NULL',
            'password' => 'required|string|min:8',
            'image' => 'nullable|mimes:png,jpg,jpeg',
            'role' => 'required',
            'allow_all_zones' => 'nullable|boolean',
            'zone_ids' => [
                'required_if:allow_all_zones,0',
                'array',
                Rule::exists('zones', 'id'),
            ],
            'zone_ids.*' => 'exists:zones,id',
        ];
    }

    public function messages()
    {
        return [
            'zone_ids.required_if' => 'Please select at least one zone or enable "Allow All Zones".',
        ];
    }
}

