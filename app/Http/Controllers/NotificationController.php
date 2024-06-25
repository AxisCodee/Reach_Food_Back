<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Helpers\ResponseHelper;
use App\Http\Resources\Notifications\DashboardNotifications;
use App\Http\Resources\Notifications\MobileNotifications;
use App\Models\User;

class NotificationController extends Controller
{
    public function index()
    {
//        $user = auth()->user();todo
        $user = User::find(20);

        $notifications = $user['notifications'];
        if($user['role'] == Roles::SALESMAN->value || $user['role'] == Roles::CUSTOMER->value){
            return ResponseHelper::success(
                MobileNotifications::collection($notifications)
            );
        }
        else{
            return ResponseHelper::success(
                DashboardNotifications::collection($notifications)
            );
        }
    }
}
