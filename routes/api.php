<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\UserController;
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

Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');

Route::prefix('user')->group(function () {
    Route::apiResource('users', UserController::class)
        ->only('index');
        Route::get('permissions', [UserController::class, 'getPermissions']);


});


Route::prefix('branch')->group(function () {
    Route::apiResource('branches', BranchController::class)
        ->only('store', 'index');
    Route::get('show/{id}', [BranchController::class, 'show']);
    Route::post('/{id}', [BranchController::class, 'update']);
    Route::delete('/{id}', [BranchController::class, 'destroy']);

});

Route::prefix('product')->group(function () {
    Route::apiResource('products', ProductController::class)->only('store', 'index');
    Route::post('products/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    Route::get('show/{id}', [ProductController::class, 'show']);

});

//categories
Route::apiResource('category', CategoryController::class)->only('store', 'index');
Route::prefix('category')->group(function () {
    Route::post('/{id}', [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
    Route::get('/{id}', [CategoryController::class, 'show']);
});

Route::prefix('feedback')->group(function () {
    Route::controller(FeedbackController::class)->group(function () {
        Route::delete('feedback/{id}', [FeedbackController::class, 'destroy']);
        Route::apiResource('feedback', FeedbackController::class)->only('store', 'index')->middleware('auth:sanctum');
    });
});

Route::prefix('trip')->group(function () {
    Route::controller(TripController::class)->group(function () {
    });
});


Route::post('importFromJson', [AddressController::class, 'importFromJson']);
