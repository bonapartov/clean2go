<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreateWalletBonusRequest extends FormRequest
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
            /** Translatable fields **/
            'locale'              => ['required', 'string'],
            'name'                => ['required', 'string', 'max:255'],
            'description'         => ['required', 'string'],
            'usage_limit_per_user' => ['required_if:is_unlimited,0', 'nullable', 'numeric'],
            'total_usage_limit' => ['required_if:is_unlimited,0', 'nullable', 'numeric'],

            /** Type **/
            'type'                => ['required', 'in:fixed,percentage'],

            /** Fixed type **/
            'amount'              => [
                'nullable',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($this->type === 'fixed' && empty($value)) {
                        $fail(__('validation.amount_required'));
                    }
                }
            ],

            /** Percentage type **/
            'percentage_amount'   => [
                'nullable',
                'numeric',
                'min:1',
                'max:100',
                function ($attribute, $value, $fail) {
                    if ($this->type === 'percentage' && empty($value)) {
                        $fail(__('validation.percentage_amount_required'));
                    }
                }
            ],

            /** Amount logic **/
            'min_top_up_amount'   => ['required', 'numeric', 'min:1'],
            'max_bonus'           => ['required', 'numeric', 'min:0'],

            /** Switches **/
            'status'              => ['nullable', 'in:0,1'],
            'is_admin_funded'     => ['nullable', 'in:0,1'],
            'is_unlimited'      => ['nullable', 'in:0,1'],
        ];
    }

    public function messages()
    {
        return [
            /** Type **/
            'type.in' => __('validation.type_in_wallet_bonus'),

            /** Amount messages **/
            'amount.min'                     => __('validation.amount_min'),
            'percentage_amount.min'          => __('validation.percentage_min'),
            'percentage_amount.max'          => __('validation.percentage_max'),

            /** Required specific messages **/
            'min_top_up_amount.required'     => __('validation.min_top_up_required'),
            'max_bonus.required'             => __('validation.max_bonus_required'),
            'total_usage_limit'              => __('validation.total_usage_limit'),
            'usage_limit_per_user'           => __('validation.usage_limit_per_user'), 
        ];
    }
}
