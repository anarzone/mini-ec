<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can retrieve products without facets by default', function () {
    Product::factory()
        ->laptop()
        ->has(ProductVariant::factory()->forLaptop()->count(2), 'variants')
        ->count(3)
        ->create();

    $response = $this->getJson('/api/products');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'data' => [
                'data',
                'current_page',
                'total',
            ],
        ])
        ->assertJsonMissing(['facets']);
});

it('can retrieve products with all facets when include_facets is true', function () {
    // Create laptops with different brands
    Product::factory()
        ->laptop()
        ->state(['brand' => 'Apple'])
        ->has(ProductVariant::factory()->forLaptop()->count(2), 'variants')
        ->count(2)
        ->create();

    Product::factory()
        ->laptop()
        ->state(['brand' => 'Dell'])
        ->has(ProductVariant::factory()->forLaptop()->count(2), 'variants')
        ->count(3)
        ->create();

    $response = $this->getJson('/api/products?include_facets=true');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'data',
            'facets' => [
                'brands',
                'categories',
                'price',
                'product_attributes',
                'variant_attributes',
            ],
        ]);

    $facets = $response->json('facets');

    expect($facets['brands'])->toBeArray()
        ->and($facets['brands']['Apple'])->toBe(2)
        ->and($facets['brands']['Dell'])->toBe(3)
        ->and($facets['price'])->toHaveKeys(['min', 'max', 'avg']);
});

it('can retrieve specific facets when facets array is provided', function () {
    Product::factory()
        ->laptop()
        ->state(['brand' => 'Apple'])
        ->has(ProductVariant::factory()->forLaptop()->count(2), 'variants')
        ->count(2)
        ->create();

    $response = $this->getJson('/api/products?include_facets=true&facets[]=brands&facets[]=price');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'data',
            'facets' => [
                'brands',
                'price',
            ],
        ]);

    $facets = $response->json('facets');

    expect($facets)->toHaveKeys(['brands', 'price'])
        ->and($facets)->not->toHaveKey('categories')
        ->and($facets)->not->toHaveKey('product_attributes')
        ->and($facets)->not->toHaveKey('variant_attributes');
});

it('facets reflect applied brand filters', function () {
    // Create Apple and Dell laptops
    Product::factory()
        ->laptop()
        ->state(['brand' => 'Apple'])
        ->has(ProductVariant::factory()->forLaptop()->count(2), 'variants')
        ->count(5)
        ->create();

    Product::factory()
        ->laptop()
        ->state(['brand' => 'Dell'])
        ->has(ProductVariant::factory()->forLaptop()->count(2), 'variants')
        ->count(3)
        ->create();

    // Filter by Apple brand
    $response = $this->getJson('/api/products?include_facets=true&brands[]=Apple');

    $response->assertSuccessful();

    $facets = $response->json('facets');

    // Should only show Apple in brands facet
    expect($facets['brands'])->toHaveKey('Apple')
        ->and($facets['brands'])->not->toHaveKey('Dell')
        ->and($facets['brands']['Apple'])->toBe(5);
});

it('facets reflect applied category filters', function () {
    $laptopCategory = Category::factory()->create(['name' => 'Laptops']);
    $phonesCategory = Category::factory()->create(['name' => 'Phones']);

    // Create products in different categories
    $laptops = Product::factory()
        ->laptop()
        ->state(['brand' => 'Apple'])
        ->has(ProductVariant::factory()->forLaptop()->count(2), 'variants')
        ->count(3)
        ->create();

    $phones = Product::factory()
        ->state(['brand' => 'Samsung'])
        ->has(ProductVariant::factory()->count(2), 'variants')
        ->count(2)
        ->create();

    // Attach categories
    foreach ($laptops as $laptop) {
        $laptop->categories()->attach($laptopCategory->id);
    }

    foreach ($phones as $phone) {
        $phone->categories()->attach($phonesCategory->id);
    }

    // Filter by laptop category
    $response = $this->getJson("/api/products?include_facets=true&category_id={$laptopCategory->id}");

    $response->assertSuccessful();

    $facets = $response->json('facets');
    $data = $response->json('data.data');

    // Should only show laptops
    expect($data)->toHaveCount(3)
        ->and($facets['brands'])->toHaveKey('Apple')
        ->and($facets['brands'])->not->toHaveKey('Samsung');
});

it('price facet shows correct min, max, and average', function () {
    // Create products with known prices
    $product1 = Product::factory()->laptop()->create();
    ProductVariant::factory()->for($product1)->create(['price_cents' => 100000]); // $1000

    $product2 = Product::factory()->laptop()->create();
    ProductVariant::factory()->for($product2)->create(['price_cents' => 200000]); // $2000

    $product3 = Product::factory()->laptop()->create();
    ProductVariant::factory()->for($product3)->create(['price_cents' => 300000]); // $3000

    $response = $this->getJson('/api/products?include_facets=true');

    $response->assertSuccessful();

    $priceFacet = $response->json('facets.price');

    expect($priceFacet['min'])->toBe(100000)
        ->and($priceFacet['max'])->toBe(300000)
        ->and($priceFacet['avg'])->toBe(200000);
});

it('product attributes facet shows correct counts', function () {
    // Create laptops with specific processors
    Product::factory()
        ->laptop()
        ->state(['attributes' => ['processor' => 'M3 Pro', 'screen_size' => '14 inch']])
        ->has(ProductVariant::factory()->forLaptop()->count(1), 'variants')
        ->count(3)
        ->create();

    Product::factory()
        ->laptop()
        ->state(['attributes' => ['processor' => 'Intel i7', 'screen_size' => '14 inch']])
        ->has(ProductVariant::factory()->forLaptop()->count(1), 'variants')
        ->count(2)
        ->create();

    $response = $this->getJson('/api/products?include_facets=true');

    $response->assertSuccessful();

    $productAttributesFacet = $response->json('facets.product_attributes');

    expect($productAttributesFacet)->toHaveKey('processor')
        ->and($productAttributesFacet['processor']['M3 Pro'])->toBe(3)
        ->and($productAttributesFacet['processor']['Intel i7'])->toBe(2)
        ->and($productAttributesFacet)->toHaveKey('screen_size')
        ->and($productAttributesFacet['screen_size']['14 inch'])->toBe(5);
});

it('variant attributes facet shows correct counts', function () {
    // Create laptop variants with specific attributes
    $product1 = Product::factory()->laptop()->create();
    ProductVariant::factory()
        ->for($product1)
        ->state(['attributes' => ['color' => 'Silver', 'ram_gb' => 16]])
        ->count(2)
        ->create();

    $product2 = Product::factory()->laptop()->create();
    ProductVariant::factory()
        ->for($product2)
        ->state(['attributes' => ['color' => 'Black', 'ram_gb' => 16]])
        ->count(1)
        ->create();

    ProductVariant::factory()
        ->for($product2)
        ->state(['attributes' => ['color' => 'Silver', 'ram_gb' => 32]])
        ->count(1)
        ->create();

    $response = $this->getJson('/api/products?include_facets=true');

    $response->assertSuccessful();

    $variantAttributesFacet = $response->json('facets.variant_attributes');

    expect($variantAttributesFacet)->toHaveKey('color')
        ->and($variantAttributesFacet['color']['Silver'])->toBe(3)
        ->and($variantAttributesFacet['color']['Black'])->toBe(1)
        ->and($variantAttributesFacet)->toHaveKey('ram_gb')
        ->and($variantAttributesFacet['ram_gb']['16'])->toBe(3)
        ->and($variantAttributesFacet['ram_gb']['32'])->toBe(1);
});

it('facets work with search query', function () {
    Product::factory()
        ->laptop()
        ->state(['title' => 'MacBook Pro', 'brand' => 'Apple'])
        ->has(ProductVariant::factory()->forLaptop()->count(2), 'variants')
        ->count(2)
        ->create();

    Product::factory()
        ->laptop()
        ->state(['title' => 'Dell XPS', 'brand' => 'Dell'])
        ->has(ProductVariant::factory()->forLaptop()->count(2), 'variants')
        ->count(1)
        ->create();

    Product::factory()
        ->laptop()
        ->state(['title' => 'ThinkPad', 'brand' => 'Lenovo'])
        ->has(ProductVariant::factory()->forLaptop()->count(2), 'variants')
        ->count(1)
        ->create();

    // Search should work with facets
    $response = $this->getJson('/api/products?q=MacBook&include_facets=true');

    $response->assertSuccessful();

    $facets = $response->json('facets');

    // Facets should reflect search results
    expect($facets['brands'])->toHaveKey('Apple');
});

it('facets work with multiple filters combined', function () {
    $category = Category::factory()->create(['name' => 'Laptops']);

    // Create Apple laptops with M3 Pro
    $appleProducts = Product::factory()
        ->laptop()
        ->state(['brand' => 'Apple', 'attributes' => ['processor' => 'M3 Pro']])
        ->has(ProductVariant::factory()->forLaptop()->state(['price_cents' => 150000])->count(2), 'variants')
        ->count(2)
        ->create();

    foreach ($appleProducts as $product) {
        $product->categories()->attach($category->id);
    }

    // Create Dell laptops with Intel i7
    $dellProducts = Product::factory()
        ->laptop()
        ->state(['brand' => 'Dell', 'attributes' => ['processor' => 'Intel i7']])
        ->has(ProductVariant::factory()->forLaptop()->state(['price_cents' => 100000])->count(2), 'variants')
        ->count(3)
        ->create();

    foreach ($dellProducts as $product) {
        $product->categories()->attach($category->id);
    }

    // Filter by brand and price
    $response = $this->getJson("/api/products?include_facets=true&brands[]=Apple&price_min=140000&category_id={$category->id}");

    $response->assertSuccessful();

    $facets = $response->json('facets');
    $data = $response->json('data.data');

    // Should only show Apple products
    expect($data)->toHaveCount(2)
        ->and($facets['brands'])->toHaveKey('Apple')
        ->and($facets['brands'])->not->toHaveKey('Dell')
        ->and($facets['price']['min'])->toBe(150000);
});

it('validates facets parameter must be an array', function () {
    Product::factory()->laptop()->has(ProductVariant::factory()->forLaptop()->count(1), 'variants')->create();

    $response = $this->getJson('/api/products?include_facets=true&facets=invalid');

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['facets']);
});

it('validates facets array values must be valid facet names', function () {
    Product::factory()->laptop()->has(ProductVariant::factory()->forLaptop()->count(1), 'variants')->create();

    $response = $this->getJson('/api/products?include_facets=true&facets[]=brands&facets[]=invalid_facet');

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['facets.1']);
});

it('returns empty facets when no products exist', function () {
    $response = $this->getJson('/api/products?include_facets=true');

    $response->assertSuccessful();

    $facets = $response->json('facets');

    expect($facets['brands'])->toBeArray()->toBeEmpty()
        ->and($facets['categories'])->toBeArray()->toBeEmpty()
        ->and($facets['price'])->toBe(['min' => 0, 'max' => 0, 'avg' => 0]);
});
