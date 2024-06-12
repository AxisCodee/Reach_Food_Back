<?php

namespace App\Services;

use App\Models\Notification;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use Kreait\Firebase\Factory;

class FcmNotificationService
{
    protected Messaging $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
        $this->messaging = $factory->createMessaging();
    }


    public function sendNotification($deviceToken, $title, $body): array
    {
        $notification = FcmNotification::create($title, $body);
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification);
        return $this->messaging->send($message);
    }


    public function createNotification($data)
    {
        $notification = Notification::create([
                'type' => $data['type'],
                'user_id' => $data['user_id'],
            ]
        );

        return $notification;
    }
}
