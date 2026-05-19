<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;


class UpdateWalletBonusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'locale'            => 'required|string',
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',

            'type'              => 'required|in:fixed,percentage',

            'amount'            => 'required_if:type,fixed|nullable|numeric|min:0',
            'percentage_amount' => 'required_if:type,percentage|nullable|numeric|min:0|max:100',

            'min_top_up_amount' => 'required|numeric|min:0',
            'max_bonus'         => 'required|numeric|min:0',

            'status'            => 'required|in:0,1',
            'is_admin_funded'   => 'required|in:0,1',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        dd([
            'input'  => $this->all(),
            'errors' => $validator->errors()->toArray(),
        ]);
    }
}
