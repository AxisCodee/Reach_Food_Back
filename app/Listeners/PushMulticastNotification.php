<?php

namespace App\Listeners;

use App\Events\SendMulticastNotification;
use App\Models\Notification;
use App\Services\DeviceTokensService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;


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
    public function handle(SendMulticastNotification $event): void
    {
        DB::transaction(function () use ($event) {
            $data = [
                'action_type' => $event->action,
                'actionable_id' => $event->actionModel['id'] ?? null,
                'actionable_type' => is_object($event->actionModel) ? get_class((object)$event->actionModel) : null,
                'user_id' => $event->userId,
                'branch_id' => $event->branchId,
            ];

            $notificationService = NotificationService::make($data, $event->firstOrCreate, $event->ownerIds);
            if (!$notificationService)
                return;
            $title = $notificationService->getTitle();
            $body = $notificationService->getContent();


            $tokensService = new DeviceTokensService();
            $deviceTokens = $tokensService->get($event->ownerIds);
            $fcmNotification = FirebaseNotification::create($title, $body);
            $message = CloudMessage::new()
                ->withNotification($fcmNotification)
                ->withDefaultSounds();
            try {
                $this->messaging->sendMulticast($message, $deviceTokens);
            } catch (MessagingException|FirebaseException) {
            }
        });
    }
}
