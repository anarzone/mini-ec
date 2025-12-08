<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductVariantFormRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;

class ProductVariantsController extends Controller
{
    public function update(ProductVariantFormRequest $request, Product $product, ProductVariant $variant): JsonResponse
    {
        if ($variant->product_id !== $product->id) {
            return response()->json([
                'message' => 'Variant does not belong to this product'
            ], 404);
        }

        $variant->update($request->validated());

        return response()->json([
            'message' => 'Variant updated successfully',
            'data' => $variant->fresh()
        ]);
    }
}
