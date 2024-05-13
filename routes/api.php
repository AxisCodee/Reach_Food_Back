<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\UserDetailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
        Route::get('logout', 'logout');
        Route::get('refresh', 'refresh');
    });
});


Route::prefix('user')->group(function () {
    Route::controller(UserDetailController::class)->group(function () {
        Route::post('store', 'store');
    });
});


Route::prefix('order')->group(function () {
    Route::controller(OrderController::class)->group(function () {
    });
});

Route::prefix('product')->group(function () {
    Route::controller(ProductController::class)->group(function () {
        Route::apiResource('products', ProductController::class)->only('store', 'show');
        Route::post('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);



    });
});

Route::prefix('feedback')->group(function () {
    Route::controller(FeedbackController::class)->group(function () {
        Route::delete('feedback/{id}', [FeedbackController::class, 'destroy']);
        Route::apiResource('feedback',FeedbackController::class)->only('store', 'show')->middleware('auth:sanctum');
    });
});

Route::prefix('trip')->group(function () {
    Route::controller(TripController::class)->group(function () {
    });
});
