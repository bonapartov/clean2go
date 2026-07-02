<?php

namespace App\Http\Requests\Backend;

use App\Models\Zone;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaxRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'regex:/^([0-9]{1,2}){1}(\.[0-9]{1,2})?$/'],
            'zone_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value === 'all') {
                        return;
                    }
                    if (! Zone::whereKey($value)->exists()) {
                        $fail(__('validation.exists', ['attribute' => 'zone id']));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'rate.regex' => 'Enter Tax Rate between 0 to 99.99',
        ];
    }
}
