<?php


use App\Http\Controllers\ProductsController;

Route::apiResource('/products', ProductsController::class);
Route::apiResource('/orders', ProductsController::class);
