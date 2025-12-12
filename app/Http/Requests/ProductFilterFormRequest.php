<?php

namespace App\Http\Requests;

use App\Dtos\ProductFilterDto;
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

            // Product attributes (shared specs like processor, screen_size)
            'product_attributes' => ['sometimes', 'array', 'max:50'],
            'product_attributes.*' => ['string', 'max:255'],

            // Variant attributes (color, size, RAM, storage)
            'variant_attributes' => ['sometimes', 'array', 'max:50'],
            'variant_attributes.*' => ['max:255'],

            // Facets
            'facets' => ['sometimes', 'array', 'max:10'],
            'facets.*' => ['string', 'in:brands,categories,price,product_attributes,variant_attributes'],
        ];
    }

    public function toDto(): ProductFilterDto
    {
        $validated = $this->validated();

        return ProductFilterDto::fromArray($validated);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $allowedKeys = ['q', 'categories', 'brands', 'category_id', 'product_attributes', 'variant_attributes', 'price_min', 'price_max', 'attributes', 'facets', 'XDEBUG_SESSION', 'page'];
            $extraKeys = array_diff(array_keys($this->all()), $allowedKeys);

            if (count($extraKeys) > 0) {
                $validator->errors()->add(
                    'invalid_parameters',
                    'The following parameters are not allowed: '.implode(', ', $extraKeys)
                );
            }
        });
    }
}
