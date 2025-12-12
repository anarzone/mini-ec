<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FacetService
{
    /**
     * Generate facets from a product query builder
     *
     * @param  Builder  $query  The base query (before pagination)
     */
    public function generateFacets(Builder $query): array
    {
        $facets = [];

        $facets['brands'] = $this->getBrandsFacet(clone $query);
        $facets['categories'] = $this->getCategoriesFacet(clone $query);
        $facets['price'] = $this->getPriceFacet(clone $query);
        $facets['product_attributes'] = $this->getProductAttributesFacet(clone $query);
        $facets['variant_attributes'] = $this->getVariantAttributesFacet(clone $query);

        return $facets;
    }

    /**
     * Get brands facet with counts
     */
    protected function getBrandsFacet(Builder $query): array
    {
        return $query
            ->select('brand', DB::raw('COUNT(*) as count'))
            ->whereNotNull('brand')
            ->groupBy('brand')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'brand')
            ->toArray();
    }

    /**
     * Get categories facet with counts
     */
    protected function getCategoriesFacet(Builder $query): array
    {
        return $query
            ->join('category_product', 'products.id', '=', 'category_product.product_id')
            ->join('categories', 'category_product.category_id', '=', 'categories.id')
            ->select('categories.name', 'categories.id', DB::raw('COUNT(DISTINCT products.id) as count'))
            ->groupBy('categories.id')
            ->orderBy('count', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->name => [
                    'id' => $item->id,
                    'count' => $item->count,
                ]];
            })
            ->toArray();
    }

    /**
     * Get price range facet (min, max, and distribution)
     */
    protected function getPriceFacet(Builder $query): array
    {
        // Get min and max prices from variants
        $priceStats = $query
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->select(
                DB::raw('MIN(product_variants.price_cents) as min_price'),
                DB::raw('MAX(product_variants.price_cents) as max_price'),
                DB::raw('AVG(product_variants.price_cents) as avg_price')
            )
            ->first();

        if (! $priceStats || $priceStats->min_price === null) {
            return [
                'min' => 0,
                'max' => 0,
                'avg' => 0,
            ];
        }

        return [
            'min' => (int) $priceStats->min_price,
            'max' => (int) $priceStats->max_price,
            'avg' => (int) round($priceStats->avg_price),
        ];
    }

    /**
     * Get product attributes facet (dynamic JSON attributes)
     */
    protected function getProductAttributesFacet(Builder $query): array
    {
        // Get all products with attributes
        $products = $query
            ->whereNotNull('attributes')
            ->get(['attributes']);

        return $this->getAttributeCounts($products);
    }

    /**
     * Get variant attributes facet (colors, sizes, RAM, storage, etc.)
     */
    protected function getVariantAttributesFacet(Builder $query): array
    {
        // Get all variants for the filtered products
        $variants = $query
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->whereNotNull('product_variants.attributes')
            ->select('product_variants.attributes')
            ->get();

        return $this->getAttributeCounts($variants);
    }

    private function getAttributeCounts(Collection $products): array
    {
        $attributeCounts = [];

        foreach ($products as $product) {
            if (! is_array($product->attributes)) {
                continue;
            }

            foreach ($product->attributes as $key => $value) {
                if (! isset($attributeCounts[$key])) {
                    $attributeCounts[$key] = [];
                }

                // Handle both single values and arrays
                $values = is_array($value) ? $value : [$value];

                foreach ($values as $v) {
                    $stringValue = (string) $v;
                    if (! isset($attributeCounts[$key][$stringValue])) {
                        $attributeCounts[$key][$stringValue] = 0;
                    }
                    $attributeCounts[$key][$stringValue]++;
                }
            }
        }

        // Sort each attribute's values by count
        foreach ($attributeCounts as $key => $values) {
            arsort($attributeCounts[$key]);
        }

        return $attributeCounts;
    }
}
