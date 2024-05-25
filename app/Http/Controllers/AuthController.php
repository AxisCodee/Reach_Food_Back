<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Helpers\ResponseHelper;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use App\Models\UserPermission;
use App\Services\TripService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(CreateUserRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'user_name' => $request->user_name,
                'password' => Hash::make($request['password']),
                'role' => $request->role,
            ]);
            $this->userService->createUserDetails($request, $user->id);
            //permissions
            foreach ($request['permission_id'] as $index => $permissionId) {
                $status = $request['status'][$index];
                UserPermission::create([
                    'permission_id' => $permissionId,
                    'user_id' => $user->id,
                    'status' => $status
                ]);
            }
            if ($request->role != Roles::CUSTOMER->value) {
                if ($request->role != Roles::SUPER_ADMIN->value) {
                    $user->update([
                        'branch_id' => $request->branch_id
                    ]);
                    if ($request->role == Roles::ADMIN->value) {
                        $user->update([
                            'superAdmin_id' => auth('sanctum')->id()
                        ]);
                    }
                    if ($request->role == Roles::SALES_MANAGER->value) {
                        //link with salesmen
                        $salesmen = $request['salesmen'];
                        if ($salesmen) {
                            foreach ($salesmen as $salesman) {
                                $this->userService->linkWithSalesManager($salesman, $user->id);
                            }
                        }
                    }
                    if ($request->role == Roles::SALESMAN->value) {
                        //create trips
                        $user->update([
                            'salesManager_id' => $request->salesManager_id,
                        ]);
                        $trips = $request['trips'];
                        foreach ($trips as $trip) {
                            $trip = app(TripService::class)->createTrip($trip);
                            $this->userService->linkTripWithSalesman($trip, $user->id);
                        }
                        // link with categories
                        $categories = $request['categories'];
                        $user->categories()->attach($categories);
                    }
                }
            }
            if ($request->role == Roles::CUSTOMER->value) {
                $user->update([
                    'customer_type' => $request->customer_type,
                ]);
            }
            $token = $user->createToken('auth_token')->plainTextToken;
            return ResponseHelper::success([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        });
    }

    public function login(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string|max:255|exists:users',
            'password' => 'required|string',
        ]);
        $user = User::where('user_name', $request->user_name)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return ResponseHelper::error('Invalid username or password.', 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return ResponseHelper::success([
            'user' => $user->with(['contacts', 'userDetails', 'userDetails.address',
                'userDetails.address.city',
                'userDetails.address.city.country'])->find($user->id),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout()
    {
        $user = auth('sanctum')->user();
        if ($user) {
            $user->tokens()->delete();
            return ResponseHelper::success('Logged out successfully.');
        }
        return ResponseHelper::error('You are not authorized.', 401);
    }

    public function refresh()//TODO
    {
        return DB::transaction(function () {
            auth('sanctum')->user()->tokens()->delete();
            $token = auth('sanctum')->user()->createToken('auth_token')->plainTextToken;
            return ResponseHelper::success([
                'user' => auth('sanctum')->user(),
                'token' => $token,
            ]);
        });
    }

    public function me()
    {
        $user = Auth::user();
        if ($user) {
            return ResponseHelper::success([auth('sanctum')->user()]);
        }
        return ResponseHelper::error('You are not authorized.', 401);
    }

}
