<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class OpenAIGenerateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'input_text'   => 'required|string|min:2',
            'locale'       => 'required|string|size:2',
            'type'         => 'required|string|in:service_title',
        ];
    }

    public function messages()
    {
        return [
            'input_text.required' => __('validation.openai.input_text_required'),
            'input_text.min'      => __('validation.openai.input_text_min'),

            'locale.required'     => __('validation.openai.locale_required'),
            'locale.size'         => __('validation.openai.locale_size'),

            'type.required'       => __('validation.openai.type_required'),
            'type.in'             => __('validation.openai.type_invalid'),
        ];
    }
}
