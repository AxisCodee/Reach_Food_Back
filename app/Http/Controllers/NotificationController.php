<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Helpers\ResponseHelper;
use App\Http\Resources\Notifications\DashboardNotifications;
use App\Http\Resources\Notifications\MobileNotifications;
use App\Models\Branch;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Trip;
use App\Models\User;
use App\Services\TripService;
use function GuzzleHttp\default_user_agent;

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

    public function back(TripService $tripService, $id)
    {
        $notification = Notification::query()->find($id);
        if($notification['action_type'] != 'delete'){
            return ResponseHelper::error('can not back this action');
        }
        $actionableType = $notification['actionable_type'];
        if(in_array($actionableType, [
            Product::class,
            Branch::class,
            User::class,
        ])){
            $notification->actionable->restore();
            return ResponseHelper::success('success back');
        }elseif($actionableType == Trip::class){
            if($tripService->conflicts($notification['actionable'])){
                return ResponseHelper::error('لا يمكن ارجاع هذه الرحلة');
            }
            $notification['actionable']->restore();
            return ResponseHelper::success('success back');
        }else{
            return ResponseHelper::error('لا يمكن التراجع عن هذا الحدث');
        }

    }
}
