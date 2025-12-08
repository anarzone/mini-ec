<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductVariantFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function expectsJson(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $variantId = $this->route('variant')?->id;

        return [
            'sku' => [
                'sometimes',
                'string',
                'max:64',
                $variantId ? "unique:product_variants,sku,$variantId" : "unique:product_variants,sku"
            ],
            'price_cents' => ['sometimes', 'integer', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3', 'uppercase'],
            'weight_g' => [ 'nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'attributes' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'SKU is required for the variant.',
            'sku.unique' => 'This SKU is already in use by another variant.',
            'price_cents.required' => 'Price is required.',
            'price_cents.min' => 'Price cannot be negative.',
            'currency.size' => 'Currency must be a 3-letter code (e.g., USD, EUR).',
            'currency.uppercase' => 'Currency code must be uppercase.',
            'weight_g.min' => 'Weight cannot be negative.',
        ];
    }
}
