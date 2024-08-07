<?php

use App\Enums\Roles;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CityController;
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
use App\Models\Product;
use App\Models\Trip;
use App\Models\TripTrace;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\TripTraceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;


Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/correct-password', [AuthController::class, 'correctPassword']);

    Route::prefix('auth')->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::post('register', 'register')
                ->middleware('roles:super admin,admin,sales manager,salesman');
            Route::get('logout', 'logout');
            Route::get('refresh', 'refresh');
        });
    });

    Route::get('me', [AuthController::class, 'me']);

    Route::prefix('user')->group(function () {
        Route::apiResource('users', UserController::class)
            ->only('index', 'destroy')
            ->middleware('roles:super admin,admin,sales manager,salesman');
        Route::post('update/{id}', [UserController::class, 'update']);
        Route::get('show/{id}', [UserController::class, 'show']);
        Route::get('show-salesman/{id}', [UserController::class, 'showSalesMan']);
        Route::post('restore/{id}', [UserController::class, 'restore']);
        Route::get('permissions', [PermissionController::class, 'index']);
        Route::get('address', [UserController::class, 'userAddress']);

        Route::get('/admins-without-city', [UserController::class, 'adminsWithoutCity']);
//       User by role branches
        Route::get('{role}/branches', [BranchController::class, 'userBranches'])
            ->whereIn('role', [Roles::SALESMAN->value, Roles::CUSTOMER->value]);
        Route::prefix('salesman')->group(function () {
            Route::get('/customers', [UserController::class, 'getSalesmanCustomers'])
                ->middleware('roles:salesman');
        });
    });

    Route::prefix('branch')->group(function () {
        Route::apiResource('branches', BranchController::class)
            ->only('store', 'index');
        Route::get('show/{id}', [BranchController::class, 'show']);
        Route::post('/{id}', [BranchController::class, 'update']);
        Route::delete('/delete', [BranchController::class, 'delete']);
        Route::delete('/delete/{id}', [BranchController::class, 'deleteBranch']);
        Route::post('/restore/{id}', [BranchController::class, 'restore']);
        Route::get('/cities', [BranchController::class, 'branches']);
        Route::get('list', [BranchController::class, 'list']);
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
            Route::get('/{orderId}',[OrderController::class,'showForSalesman'])->whereNumber('orderId');
        });
        Route::prefix('customer')->group(function () {
            Route::get('/my-order', [OrderController::class, 'myOrder']);
        });
    });

    Route::prefix('product')->group(function () {
        Route::apiResource('products', ProductController::class)->only('store', 'index');
        Route::post('products/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
        Route::post('restore/{id}', [ProductController::class, 'restore']);
        Route::get('show/{id}', [ProductController::class, 'show']);
        Route::post('updatePrice', [ProductController::class, 'updatePrice']);
        Route::post('/import', [ProductController::class, 'importProducts']);
        Route::get('/prices', [ProductController::class, 'getPrices']);
        Route::get('/list-prices', [ProductController::class, 'listPrices']);
        Route::post('/supply', [ProductController::class, 'supply']);
        Route::prefix('salesman')->group(function () {
            Route::get('/index', [ProductController::class, 'salesmanProducts']);
        });
    });


    Route::prefix('feedback')->group(function () {
        Route::controller(FeedbackController::class)->group(function () {
            Route::delete('feedback/{id}', [FeedbackController::class, 'destroy']);
            Route::apiResource('feedback', FeedbackController::class)->only('store', 'index');
        });
    });

    Route::prefix('trip')->group(function () {
        Route::controller(TripController::class)->group(function () {
            Route::apiResource('trips', TripController::class)->only('store', 'index', 'destroy');
            Route::put('trips/{id}', 'edit');
            Route::post('/restore/{id}', 'restore');
            Route::prefix('salesman')->group(function () {
                Route::get('/index/daily', [TripController::class, 'salesmanTripsDaily']);
                Route::get('/index/weekly', [TripController::class, 'salesmanTripsWeekly']);
            });
            Route::get('/trip-dates/{tripDate}', [TripDatesController::class, 'show']);
            Route::get('/near-trip', [TripController::class, 'nearTrip']);
        });
    });

    Route::prefix('tracing')->group(function () {
        Route::controller(TripTraceController::class)->group(function () {
            Route::get('index', [TripTraceController::class, 'index']);
//        Route::post('update', [TripTraceController::class, 'updateOrCreate']);
            Route::post('{action}', [TripTraceController::class, 'tracing'])
                ->whereIn('action', ['next', 'pause', 'resume', 'end', 'stop']);
        });
    });

/////
    Route::post('importFromJson', [AddressController::class, 'importFromJson']);


    Route::prefix('notifications')->group(function () {
        Route::get('', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unReadCounter']);
        Route::post('/back/{id}', [NotificationController::class, 'back']);
    });

    Route::prefix('cities')->group(function () {
        Route::get('/', [CityController::class, 'index']);
        Route::get('/without-admin', [CityController::class, 'citiesWithoutAdmin']);
        Route::get('/without-branches', [CityController::class, 'citiesWithoutBranches']);
        Route::post('/', [CityController::class, 'store']);
        Route::put('/{city}', [CityController::class, 'update']);
        Route::delete('/{city}', [CityController::class, 'delete']);
    })->middleware('roles:super admin');
});
Route::get('/test', function () {
    return User::query()
        ->get();
});
