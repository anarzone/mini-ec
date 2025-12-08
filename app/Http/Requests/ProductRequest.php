<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $productId = $this->route('product');

        return [
            'title' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => [$isUpdate ? 'sometimes' : 'required', 'string'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                $productId ? "unique:products,slug,$productId" : 'unique:products,slug'
            ],
            'brand' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:100'],
            'attributes' => ['nullable', 'array'],
            "categories" => ['sometimes', 'array','max:10'],
            "categories.*" => ['sometimes', 'integer'],

            // Variants - only for create (store), not for update
            'variants' => ['sometimes', 'array', 'max:50'],
            'variants.*.sku' => ['required', 'string', 'max:64', 'distinct'],
            'variants.*.price_cents' => ['required', 'integer', 'min:0'],
            'variants.*.currency' => ['required', 'string', 'size:3', 'uppercase'],
            'variants.*.weight_g' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_active' => ['sometimes', 'boolean'],
            'variants.*.attributes' => ['nullable', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
