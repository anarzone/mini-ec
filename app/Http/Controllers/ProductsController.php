<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFilterFormRequest;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ProductsController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(ProductFilterFormRequest $request): JsonResponse
    {
        $result = $this->productService->getProducts($request->toDto());

        $response = [
            'message' => 'Products retrieved successfully',
            'data' => $result['products'],
        ];

        if ($result['facets'] !== null) {
            $response['facets'] = $result['facets'];
        }

        return response()->json($response);
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        // Generate slug from title if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $product = \DB::transaction(function () use ($data) {
            // Create product
            $product = Product::create(collect($data)->except(['variants', 'categories'])->toArray());

            // Create variants if provided
            if (isset($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $product->variants()->create($variantData);
                }
            }

            // Sync categories if provided
            if (isset($data['categories'])) {
                $product->categories()->sync($data['categories']);
            }

            return $product;
        });

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product->load(['variants', 'categories']),
        ], Response::HTTP_CREATED);
    }

    public function show(Product $product)
    {
        return response()->json([
            'message' => 'Product retrieved successfully',
            'data' => $product->load(['variants', 'categories']),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();

        if (empty($data['slug']) && isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $product->update($data);

        // Sync categories if provided
        if (isset($data['categories'])) {
            $product->categories()->sync($data['categories']);
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product->load(['variants', 'categories']),
        ]);
    }

    public function destroy(Product $product)
    {
        \DB::transaction(function () use ($product) {
            // Delete all variants first (if not using cascade)
            $product->variants()->delete();

            // Delete the product
            $product->delete();
        });

        return response()->json(['message' => 'Product deleted successfully'], Response::HTTP_OK);
    }
}
