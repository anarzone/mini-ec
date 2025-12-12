<?php

namespace App\Services;

use App\Dtos\ProductFilterDto;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

readonly class ProductService
{
    public function __construct(private FacetService $facetService) {}

    /**
     * Get products with filters and optional facets
     * Example: $q = laptop, $brands = ['Apple'], $priceMin = 50000, $attributes = ['processor' => 'M3 Pro']
     *
     * @return array{products: LengthAwarePaginator, facets: array|null}
     */
    public function getProducts(ProductFilterDto $productFilterDto): array
    {
        $q = $productFilterDto->q;

        // Use Scout search if query provided, otherwise start with query builder
        if ($q) {
            $products = Product::search($q)->query(function ($query) use ($productFilterDto) {
                $this->getProductsQuery($productFilterDto, $query);
            });

            // Get all matching product IDs from Scout (for facets)
            $productIds = $products->keys()->all();

            // Build Eloquent query for facets using the Scout result IDs
            $facetQuery = Product::query()->whereIn('products.id', $productIds);
            $facets = $this->facetService->generateFacets($facetQuery);
        } else {
            // No search query, use regular Eloquent query
            $products = $this->getProductsQuery($productFilterDto);

            // Use cloned query for facets
            $facets = $this->facetService->generateFacets(clone $products);
        }

        $paginatedProducts = $products->paginate(perPage: 20);

        return [
            'products' => $paginatedProducts,
            'facets' => $facets,
        ];
    }

    public function getProductsQuery(ProductFilterDto $dto, ?Builder $query = null): Builder
    {
        $brands = $dto->brands;
        $categoryId = $dto->categoryId;
        $productAttributes = $dto->productAttributes;
        $variantAttributes = $dto->variantAttributes;
        $priceMin = $dto->priceMin;
        $priceMax = $dto->priceMax;

        if ($query) {
            $products = $query;
        } else {
            $products = Product::query();
        }

        $products->with(['variants', 'categories']);

        if ($brands) {
            $products->whereIn('brand', $brands);
        }

        if ($categoryId) {
            $products->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('categories.id', $categoryId);
            });
        }

        if ($productAttributes) {
            foreach ($productAttributes as $key => $value) {
                if (is_string($value)) {
                    $products->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(attributes, '$.{$key}'))) = ?", [strtolower($value)]);
                } else {
                    $products->whereJsonContains("attributes->$key", $value);
                }
            }
        }

        if ($variantAttributes) {
            $products->whereHas('variants', function ($query) use ($variantAttributes) {
                foreach ($variantAttributes as $key => $value) {
                    if (is_string($value)) {
                        $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(attributes, '$.{$key}'))) = ?", [strtolower($value)]);
                    } else {
                        $query->whereJsonContains("attributes->$key", $value);
                    }
                }
            });
        }

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

        return $products;
    }
}
