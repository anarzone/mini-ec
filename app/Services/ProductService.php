<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ProductService
{
    /**
     * Get products with filters
     * Example: $q = laptop, $brands = ['Apple'], $priceMin = 50000, $attributes = ['processor' => 'M3 Pro']
     */
    public function getProducts(Request $request): LengthAwarePaginator
    {
        $q = $request->get('q');
        $brands = $request->get('brands');
        $categoryId = $request->get('category_id');
        $productAttributes = $request->get('product_attributes');
        $variantAttributes = $request->get('variant_attributes');
        $priceMin = $request->get('price_min');
        $priceMax = $request->get('price_max');

        // Use Scout search if query provided, otherwise start with query builder
        if ($q) {
            $products = Product::search($q)->query(function ($query) use (
                $brands,
                $categoryId,
                $productAttributes,
                $variantAttributes,
                $priceMin,
                $priceMax
            ) {
                $query->with(['variants', 'categories']);

                // Filter by brands
                if ($brands) {
                    $query->whereIn('brand', $brands);
                }

                // Filter by category
                if ($categoryId) {
                    $query->whereHas('categories', function ($subQuery) use ($categoryId) {
                        $subQuery->where('categories.id', $categoryId);
                    });
                }

                // Filter by product attributes (shared specs like processor, material)
                if ($productAttributes) {
                    foreach ($productAttributes as $key => $value) {
                        $query->whereJsonContains("attributes->$key", $value);
                    }
                }

                // Filter by variant attributes (color, size, RAM, storage)
                if ($variantAttributes) {
                    $query->whereHas('variants', function ($subQuery) use ($variantAttributes) {
                        foreach ($variantAttributes as $key => $value) {
                            $subQuery->whereJsonContains("attributes->$key", $value);
                        }
                    });
                }

                // Filter by price range (on variants)
                if ($priceMin) {
                    $query->whereHas('variants', function ($subQuery) use ($priceMin) {
                        $subQuery->where('price_cents', '>=', $priceMin);
                    });
                }

                if ($priceMax) {
                    $query->whereHas('variants', function ($subQuery) use ($priceMax) {
                        $subQuery->where('price_cents', '<=', $priceMax);
                    });
                }
            });
        } else {
            // No search query, use regular Eloquent query
            $products = Product::query()->with(['variants', 'categories']);

            // Filter by brands
            if ($brands) {
                $products->whereIn('brand', $brands);
            }

            // Filter by category
            if ($categoryId) {
                $products->whereHas('categories', function ($query) use ($categoryId) {
                    $query->where('categories.id', $categoryId);
                });
            }

            // Filter by product attributes (shared specs like processor, material)
            if ($productAttributes) {
                foreach ($productAttributes as $key => $value) {
                    $products->whereJsonContains("attributes->$key", $value);
                }
            }

            // Filter by variant attributes (color, size, RAM, storage)
            if ($variantAttributes) {
                $products->whereHas('variants', function ($query) use ($variantAttributes) {
                    foreach ($variantAttributes as $key => $value) {
                        $query->whereJsonContains("attributes->$key", $value);
                    }
                });
            }

            // Filter by price range (on variants)
            if ($priceMin) {
                $products->whereHas('variants', function ($query) use ($priceMin) {
                    $query->where('price_cents', '>=', $priceMin);
                });
            }

            if ($priceMax) {
                $products->whereHas('variants', function ($query) use ($priceMax) {
                    $query->where('price_cents', '<=', $priceMax);
                });
            }
        }

        return $products->paginate(perPage: 20);
    }

    public function saveProduct()
    {
        
    }
}
