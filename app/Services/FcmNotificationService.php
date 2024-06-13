<?php

namespace App\Services;

use App\Models\Notification;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
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


    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function sendNotification(string $deviceToken, string $title, string $body): array
    {
        $notification = FcmNotification::create($title, $body);
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification)
            ->withDefaultSounds();
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

    public function sendMulticast(array $deviceTokens, string $title, string $body)
    {
        $notification = FcmNotification::create($title, $body);
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withDefaultSounds();
        $this->messaging->sendMulticast($message, $deviceTokens);
    }
}
