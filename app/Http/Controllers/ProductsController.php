<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFilterFormRequest;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductsController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(ProductFilterFormRequest $request): JsonResponse
    {
        return new JsonResponse(
            data: $this->productService->getProducts($request)
        );
    }

    public function store(ProductRequest $request)
    {
        return Product::create($request->validated());
    }

    public function show(Product $product)
    {
        return $product;
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return $product;
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json();
    }
}
