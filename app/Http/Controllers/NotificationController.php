<?php

namespace App\Http\Controllers;

use App\Services\FcmNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly FcmNotificationService $fcmService)
    {
    }

    public function send(Request $request)
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
}
