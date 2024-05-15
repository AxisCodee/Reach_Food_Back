<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserDetailController extends Controller
{
    public function show()
    {
        $result =app(UserService::class)->show();
        return ResponseHelper::success($result, null, 'products returned successfully', 200);
    }
}
