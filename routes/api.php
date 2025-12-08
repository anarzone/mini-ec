<?php

use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProductVariantsController;

Route::apiResource('/products', ProductsController::class);
Route::apiResource('products.variants', ProductVariantsController::class)->only(['update']);
Route::apiResource('/orders', ProductsController::class);
