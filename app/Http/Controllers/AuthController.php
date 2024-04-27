<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(CreateUserRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $userDetail = UserDetail::create([
                'image' => $request['image']->store('uploads/user_images', 'public'),
                'address' => $request['address'],
                'phone_number' => $request['phone_number'],
            ]);
            $user = User::create([
                'name' => $request['name'],
                'user_name' => $request['user_name'],
                'password' => Hash::make($request['password']),
                'detail_id' => $userDetail->id,
                'role' => $request['role'],
            ]);
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
            'user' => $user->userDetails,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)//TODO
    {
        auth()->logout();
        return ResponseHelper::success('Logged out successfully.');
    }

    public function refresh(Request $request)//TODO
    {
        auth()->user()->tokens()->delete();
        $token = auth()->user()->createToken('auth_token')->plainTextToken;
        return ResponseHelper::success([
            'user' => auth()->user(),
            'token' => $token,
        ]);
    }
}
