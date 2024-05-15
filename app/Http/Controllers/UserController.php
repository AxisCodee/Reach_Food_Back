<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
//    public function show()//???
//    {
//        $result =app(UserService::class)->show();
//        return ResponseHelper::success($result, null, 'products returned successfully', 200);
//    }

    public function show(Request $request)
    {
        $users = $this->userService->getUsersByType($request);
        return ResponseHelper::success($users);
    }

}
