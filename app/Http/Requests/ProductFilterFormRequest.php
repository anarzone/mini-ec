<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductFilterFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
}
