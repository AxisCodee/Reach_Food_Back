<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});
Route::prefix('area')->group(function () {
    Route::controller(AreaController::class)->group(function () {
        Route::post('store', 'store');
    });
});
Route::prefix('order')->group(function () {
    Route::controller(OrderController::class)->group(function () {
    });
});
