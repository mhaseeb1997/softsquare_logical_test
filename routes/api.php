<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Public product endpoints
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show'])->whereNumber('id');

    // Protected endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::prefix('products')->group(function () {
        Route::post('/store', [ProductController::class, 'store']);
        Route::post('/update/{id}', [ProductController::class, 'update'])->whereNumber('id');
        Route::post('/update/image/{id}', [ProductController::class, 'updateImage'])->whereNumber('id');
        Route::delete('/delete/{id}', [ProductController::class, 'destroy'])->whereNumber('id');
    });
    });
});
