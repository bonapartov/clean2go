<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CreateCustomAIModelRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'provider' => 'required|string|in:openai,google,anthropic,custom',
            'model_name' => 'nullable|string|max:255',
            'api_key' => 'nullable|string',
            'base_url' => 'nullable|url|max:500',
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
            'header_key' => 'nullable|array',
            'header_value' => 'nullable|array',
            'param_key' => 'nullable|array',
            'param_value' => 'nullable|array',
            'payload_key' => 'nullable|array',
            'payload_value' => 'nullable|array',
            'payload' => 'nullable|string|json',
        ];
    }

    /**
     * Get the validation messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('static.custom_ai_models.name')]),
            'provider.required' => __('validation.required', ['attribute' => __('static.custom_ai_models.provider')]),
            'provider.in' => __('validation.in', ['attribute' => __('static.custom_ai_models.provider')]),
            'base_url.url' => __('validation.url', ['attribute' => __('static.custom_ai_models.base_url')]),
        ];
    }
}

