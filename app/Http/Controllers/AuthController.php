<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Helpers\ResponseHelper;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginRrequest;
use App\Models\User;
use App\Services\DeviceTokensService;
use App\Services\RegistrationService;
use App\Services\UserService;
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

    public function login(LoginRrequest $request, DeviceTokensService $deviceTokensService)
    {
        $user = User::where('user_name', $request->user_name)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return ResponseHelper::error('Invalid username or password.', 401);
        }
        $token = $user->createToken('auth_token', ['*'], now()->addMinutes(10000));
        if ($request['device_token']) {
            $deviceTokensService->create($token->accessToken['id'], $request['device_token']);
        }
        $expiresAt = $token->accessToken['expires_at'];

        return ResponseHelper::success([
            'user' => $user->with(['contacts', 'address.city.country', 'branch.city'])->find($user->id),
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
        ]);
    }

    public function logout()
    {
        $user = auth('sanctum')->user();
        if ($user) {
            $user->currentAccessToken()->delete();
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
        $user = User::find(4);
        if ($user->role == Roles::SALESMAN->value) {
            $user->load('branch.city');
        }
        if ($user) {
            return ResponseHelper::success([
                $user
            ]);
        }
        return ResponseHelper::error('You are not authorized.', 401);
    }
}
