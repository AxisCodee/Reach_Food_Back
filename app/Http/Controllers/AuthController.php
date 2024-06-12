<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Helpers\ResponseHelper;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use App\Models\UserPermission;
use App\Services\DeviceTokensService;
use App\Services\FileService;
use App\Services\RegistrationService;
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
//        return DB::transaction(function () use ($request) {
//            $user = User::create([
//                'name' => $request->name,
//                'user_name' => $request->user_name,
//                'password' => Hash::make($request['password']),
//                'role' => $request->role,
//                'image' => app(FileService::class)->upload($request, 'image'),
//                'address_id' => $request->address_id,
//                'location' => $request->location,
//            ]);
//            $contacts = $request['phone_number'];
//            $this->userService->createUserContacts($contacts, $user->id);
//            if ($request->role == Roles::ADMIN->value) {
//                $user->update(['city_id' => $request->city_id]);
//            }
//            if ($request->role == Roles::CUSTOMER->value) {
//                $user->update([
//                    'customer_type' => $request->customer_type,
//                ]);
//            } else {
//                if ($request->role != Roles::SUPER_ADMIN->value) {
//                    if ($request->role == Roles::SALES_MANAGER->value) {
//                        $user->update([//link with category(branch)
//                            'branch_id' => $request->input('branch_id')
//                        ]);
//                        //link with salesmen
//                        $salesmen = $request['salesmen'];
//                        if ($salesmen) {
//                            // $user->salesManager()->attach($salesmen);
//                            $user->salesman()->attach($salesmen);
//                        }
//                    }
//                    if ($request->role == Roles::SALESMAN->value) {
//                        //assign permissions
//                        $permissions = $request['permissions'];
//                        if ($permissions) {
//                            foreach ($permissions as $index => $permission) {
//                                $status = $permission['status'];
//                                UserPermission::create([
//                                    'permission_id' => $index + 1,
//                                    'user_id' => $user->id,
//                                    'status' => $status
//                                ]);
//                            }
//                        }
//                        //TRIPS
//                       $trips = $request['trips'];
//                       if ($trips) {
//                           foreach ($trips as $trip) {
//                               $trip = app(TripService::class)->createTrip($trip);
//                               $this->userService->linkTripWithSalesman($trip, $user->id);
//                           }
//                       }
//                        // link with categories
//                        $branches = $request['branches'];
//                        if ($branches) {
//                            foreach ($branches as $branch) {
//                                $user->salesManager()->attach($branch['salesManager_id']);
//                            }
//                        }
//                    }
//                }
//            }
//            $token = $user->createToken('auth_token')->plainTextToken;
//            return ResponseHelper::success([
//                'user' => $user,
//                'access_token' => $token,
//                'token_type' => 'Bearer',
//            ]);
//        });

        $user = DB::transaction(function () use ($request) {
            $user = (new RegistrationService())->createUser($request);
            $contacts = $request['phone_number'];
            $this->userService->createUserContacts($contacts, $user->id);
            return $user;
        });
        $token = $user->createToken('auth_token')->plainTextToken;
        return ResponseHelper::success([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string|max:255|exists:users',
            'password' => 'required|string',
            'token' => 'string'
        ]);
        $user = User::where('user_name', $request->user_name)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return ResponseHelper::error('Invalid username or password.', 401);
        }
        //        $token = $user->createToken('auth_token', ['*'], now()->addMinutes(10000));
//        $deviceTokensService->create($token->accessToken['id'], $request->token);

        //$expiresAt = $user->tokens()->latest()->first()->expires_at;
        $token = $user->createToken('auth_token')->plainTextToken;
        return ResponseHelper::success([
            'user' => $user->with(['contacts', 'address.city.country'])->find($user->id),
            //'access_token' => $token->plainTextToken,
            'access_token' => $token,
            'token_type' => 'Bearer',
            //'expires_at' => $expiresAt,
        ]);
    }

    public function logout()
    {
        $user = auth('sanctum')->user();
        if ($user) {
           // $user->currentAccessToken()->delete();
            return ResponseHelper::success('Logged out successfully.');
        }
        return ResponseHelper::error('You are not authorized.', 401);
    }

    public function refresh(DeviceTokensService $deviceTokensService) //TODO
    {
        $prevToken = auth('sanctum')->user()->currentAccessToken();
        $token = auth('sanctum')->user()->createToken('auth_token');
        $deviceTokensService->update($prevToken->id, $token->accessToken['id']);
        $prevToken->delete();
        return ResponseHelper::success([
            'user' => auth('sanctum')->user(),
            'access_token' => $token->plainTextToken,
        ]);
    }

    public function me()
    {
        $user = auth('sanctum')->user();
        if ($user) {
            return ResponseHelper::success([auth('sanctum')->user()]);
        }
        return ResponseHelper::error('You are not authorized.', 401);
    }
}
