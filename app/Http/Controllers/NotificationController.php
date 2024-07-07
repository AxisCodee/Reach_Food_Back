<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Helpers\ResponseHelper;
use App\Http\Resources\Notifications\DashboardNotifications;
use App\Http\Resources\Notifications\MobileNotifications;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Traits\HasApiResponse;

class NotificationController extends Controller
{
    use HasApiResponse;

    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications(request('branch_id'))->get();
        NotificationService::setRead($user['id']);
        if ($user['role'] == Roles::SALESMAN->value || $user['role'] == Roles::CUSTOMER->value) {
            return $this->success(MobileNotifications::collection($notifications));
        } else {
            return $this->success(DashboardNotifications::collection($notifications));
        }
    }

    public function back($id)
    {
        $notification = Notification::query()->find($id);

        return NotificationService::back($notification);
    }

    public function unReadCounter()
    {
        return ResponseHelper::success(
            NotificationService::unReadCount(auth()->id())
        );
    }
}
