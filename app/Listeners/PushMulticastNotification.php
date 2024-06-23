<?php

namespace App\Listeners;

use App\Events\SendMulticastNotification;
use App\Services\DeviceTokensService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;


class PushMulticastNotification
{

    protected Messaging $messaging;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
        $this->messaging = $factory->createMessaging();
    }

    /**
     * Handle the event.
     */
    public function handle(SendMulticastNotification $event, DeviceTokensService $tokensService, NotificationService $notificationService): void
    {
        //todo get the title and body from the notification service
        $title = "hello";
        $body = "hello world";
        $deviceTokens = $tokensService->get($event->ownerIds);

        $notification = Notification::create($title, $body);
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withDefaultSounds();
        try {
            $this->messaging->sendMulticast($message, $deviceTokens);
        } catch (MessagingException|FirebaseException) {
        }
        // todo create notification

    }
}
