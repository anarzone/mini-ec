<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required','string'],
            'description' => ['required','string'],
            'slug' => ['required','string'],
            'brand' => ['required','string'],
            'attributes' => ['nullable','array'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
