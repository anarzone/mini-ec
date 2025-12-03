<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductService
{
    /**
     * Get products with filters
     * Example: $q = laptop, $brands = ['Apple'], $priceMin = 50000, $attributes = ['processor' => 'M3 Pro']
     */
    public function getProducts(Request $request): array
    {
        $products = Product::query()->with(['variants', 'categories']);

        // Text search on title and description
        if ($q = $request->get('q')) {
            $products->where(function ($query) use ($q) {
                $query->whereLike('title', "$q")
                    ->orWhereLike('description', "$q");
            });
        }

        // Filter by brands
        if ($brands = $request->get('brands')) {
            $products->whereIn('brand', $brands);
        }

        // Filter by category
        if ($categoryId = $request->get('category_id')) {
            $products->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('categories.id', $categoryId);
            });
        }

        // Filter by product attributes (shared specs like processor, material)
        if ($productAttributes = $request->get('product_attributes')) {
            foreach ($productAttributes as $key => $value) {
                $products->whereJsonContains("attributes->$key", $value);
            }
        }

        // Filter by variant attributes (color, size, RAM, storage)
        if ($variantAttributes = $request->get('variant_attributes')) {
            $products->whereHas('variants', function ($query) use ($variantAttributes) {
                foreach ($variantAttributes as $key => $value) {
                    $query->whereJsonContains("attributes->{$key}", $value);
                }
            });
        }

        // Filter by price range (on variants)
        if ($priceMin = $request->get('price_min')) {
            $products->whereHas('variants', function ($query) use ($priceMin) {
                $query->where('price_cents', '>=', $priceMin);
            });
        }

        if ($priceMax = $request->get('price_max')) {
            $products->whereHas('variants', function ($query) use ($priceMax) {
                $query->where('price_cents', '<=', $priceMax);
            });
        }

        return $products->get()->toArray();
    }
}
