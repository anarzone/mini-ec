<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProductFilterFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function expectsJson(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'string', 'max:255'],
            'categories' => ['sometimes', 'array', 'max:50'],
            'categories.*' => ['string', 'max:100'],
            'brands' => ['sometimes', 'array', 'max:50'],
            'brands.*' => ['string', 'max:100'],

            // Price Filters (in cents)
            'price_min' => ['sometimes', 'integer', 'min:1', 'max:999999999'],
            'price_max' => ['sometimes', 'integer', 'min:1', 'max:999999999'],

            // Generic attributes (for dynamic filtering)
            'attributes' => ['sometimes', 'array', 'max:50'],
            'attributes.*' => ['array', 'max:20'],
            'attributes.*.*' => ['string', 'max:100'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $allowedKeys = ['q', 'categories', 'brands', 'price_min', 'price_max', 'attributes', 'XDEBUG_SESSION', 'page'];
            $extraKeys = array_diff(array_keys($this->all()), $allowedKeys);

            if (count($extraKeys) > 0) {
                $validator->errors()->add(
                    'invalid_parameters',
                    'The following parameters are not allowed: ' . implode(', ', $extraKeys)
                );
            }
        });
    }
}
