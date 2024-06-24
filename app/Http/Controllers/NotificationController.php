<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Helpers\ResponseHelper;
use App\Http\Resources\Notifications\DashboardNotifications;
use App\Http\Resources\Notifications\MobileNotifications;
use App\Services\FcmNotificationService;
use Google\Service\Iam\Role;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly FcmNotificationService $fcmService)
    {
    }

    public function send(Request $request)//Test
    {
        $request->validate([
            'device_token' => 'required',
            'title' => 'required',
            'body' => 'required',
        ]);

        $this->fcmService->sendNotification(
            $request->input('device_token'),
            $request->input('title'),
            $request->input('body')
        );

        return response()->json(['message' => 'Notification sent successfully.']);
    }

    public function index()
    {
        $user = auth()->user();

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
