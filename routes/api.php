<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Public
|--------------------------------------------------------------------------
*/

// Public API routes for products
Route::prefix('v1')->group(function () {
    // Product/Menu endpoints
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // Order endpoints
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

Route::match(['get', 'post'], '/webhook/whatsapp', WhatsAppController::class);
