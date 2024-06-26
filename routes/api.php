<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripDatesController;
use App\Http\Controllers\TripTraceController;
use App\Http\Controllers\UserController;
use App\Models\Branch;
use App\Models\CustomerTime;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Trip;
use App\Models\TripTrace;
use App\Services\NotificationService;
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
        ->only('index', 'destroy')->middleware('auth:sanctum');
    Route::post('update/{id}', [UserController::class, 'update']);
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::get('address', [UserController::class, 'userAddress']);


    Route::prefix('salesman')->group(function () {
        Route::get('/customers', [UserController::class, 'getSalesmanCustomers']);
    });
});

Route::prefix('branch')->group(function () {
    Route::apiResource('branches', BranchController::class)
        ->only('store', 'index');
    Route::get('show/{id}', [BranchController::class, 'show']);
    Route::post('/{id}', [BranchController::class, 'update']);
    Route::delete('/delete', [BranchController::class, 'delete']);
    Route::get('/cities', [BranchController::class, 'branches']);
});

Route::prefix('address')->group(function () {
    Route::get('addresses/{id}', [AddressController::class, 'getAddresses']);
    Route::get('cities/{id}', [AddressController::class, 'getCities']);
    Route::get('countries', [AddressController::class, 'getCountries']);
    Route::get('allCities', [AddressController::class, 'allCities']);
    Route::post('deleteBranches', [AddressController::class, 'deleteBranches']);

});

Route::prefix('order')->group(function () {
    Route::post('assign', [OrderController::class, 'assignOrder']);
    Route::post('store', [OrderController::class, 'store']);
    Route::get('index', [OrderController::class, 'index']);
    Route::post('update/{id}', [OrderController::class, 'update']);
    Route::get('show/{id}', [OrderController::class, 'show']);
    Route::delete('/{id}', [OrderController::class, 'destroy']);
    Route::put('archived/{id}', [OrderController::class, 'updateStatus']);
    Route::get('cities/{id}', [AddressController::class, 'getCities']);
    Route::get('countries', [AddressController::class, 'getCountries']);
    Route::prefix('salesman')->group(function () {
        Route::get('/myOrders', [OrderController::class, 'salesmanOrders']);
    });
});

Route::prefix('product')->group(function () {
    Route::apiResource('products', ProductController::class)->only('store', 'index');
    Route::post('products/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    Route::get('show/{id}', [ProductController::class, 'show']);
    Route::post('updatePrice', [ProductController::class, 'updatePrice']);
    Route::post('/import', [ProductController::class, 'importProducts']);
    Route::prefix('salesman')->group(function () {
        Route::get('/index', [ProductController::class, 'salesmanProducts']);
    });
});


Route::prefix('feedback')->group(function () {
    Route::controller(FeedbackController::class)->group(function () {
        Route::delete('feedback/{id}', [FeedbackController::class, 'destroy']);
        Route::apiResource('feedback', FeedbackController::class)->only('store', 'index')->middleware('auth:sanctum');
    });
});

Route::prefix('trip')->group(function () {
    Route::controller(TripController::class)->group(function () {
        Route::apiResource('trips', TripController::class)->only('store', 'index','destroy');
        Route::prefix('salesman')->group(function () {
            Route::get('/index/daily', [TripController::class, 'salesmanTripsDaily']);
            Route::get('/index/weekly', [TripController::class, 'salesmanTripsWeekly']);
        });
        Route::get('/trip-dates/{tripDate}',[TripDatesController::class,'show']);
    });
});

Route::prefix('tracing')->group(function () {
    Route::controller(TripTraceController::class)->group(function () {
        Route::get('index', [TripTraceController::class, 'index']);
        Route::post('update', [TripTraceController::class, 'updateOrCreate']);
    });
});

/////
Route::post('importFromJson', [AddressController::class, 'importFromJson']);

Route::get('notifications', [NotificationController::class, 'index']);
