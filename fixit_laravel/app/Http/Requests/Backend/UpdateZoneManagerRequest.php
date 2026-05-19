<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateZoneManagerRequest extends FormRequest
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
        $id = $this->route('zone_manager')->id ?? $this->user->id;
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id.',id,deleted_at,NULL',
            'phone' => 'required|min:5|max:255|unique:users,phone,'.$id.',id,deleted_at,NULL',
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

