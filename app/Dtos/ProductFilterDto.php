<?php

namespace App\Dtos;

readonly class ProductFilterDto
{
    public function __construct(
        public ?string $q = null,
        public ?array $categories = null,
        public ?array $brands = null,
        public ?int $categoryId = null,
        public ?array $productAttributes = null,
        public ?array $variantAttributes = null,
        public ?int $priceMin = null,
        public ?int $priceMax = null,
        public ?array $attributes = null,
        public ?array $facets = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            q: $data['q'] ?? null,
            categories: $data['categories'] ?? null,
            brands: $data['brands'] ?? null,
            categoryId: $data['category_id'] ?? null,
            productAttributes: $data['product_attributes'] ?? null,
            variantAttributes: $data['variant_attributes'] ?? null,
            priceMin: $data['price_min'] ?? null,
            priceMax: $data['price_max'] ?? null,
            attributes: $data['attributes'] ?? null,
            facets: $data['facets'] ?? null,
        );
    }
}
