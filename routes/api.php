<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StorefrontController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

// Public storefront content
Route::get('/site', [StorefrontController::class, 'site']);
Route::get('/home', [StorefrontController::class, 'home']);
Route::get('/products', [StorefrontController::class, 'products']);
Route::get('/products/{slug}', [StorefrontController::class, 'product']);
Route::get('/collections/{handle}', [StorefrontController::class, 'collection']);
Route::get('/pages/{slug}', [StorefrontController::class, 'page']);
Route::post('/newsletter', [StorefrontController::class, 'subscribe'])->middleware('throttle:10,1');
Route::post('/coupons/apply', [OrderController::class, 'applyCoupon'])->middleware('throttle:30,1');

// Customer accounts
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum', 'customer'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'update']);
    Route::put('/me/password', [AuthController::class, 'password']);

    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::post('/cart/merge', [CartController::class, 'merge']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::patch('/cart/{item}', [CartController::class, 'update']);
    Route::delete('/cart/{item}', [CartController::class, 'destroy']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/merge', [WishlistController::class, 'merge']);
    Route::post('/wishlist/{productId}', [WishlistController::class, 'toggle'])->whereNumber('productId');

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{number}', [OrderController::class, 'show']);
    Route::post('/orders/{number}/cancel', [OrderController::class, 'cancel']);
});
