<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
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
            'user' => $user->with(['userDetails', 'userDetails.address', 'userDetails.address.city',
                'userDetails.address.city.country'])->find($user->id),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout()
    {
        if (auth('sanctum')->user()) {
            auth('sanctum')->user()->tokens()->delete();
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
}
