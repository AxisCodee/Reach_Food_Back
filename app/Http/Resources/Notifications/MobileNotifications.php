<?php

namespace App\Http\Resources\Notifications;

use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileNotifications extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $service = new NotificationService($this);
        return [
            'title' => $service->getTitle(),
            'content' => $service->getContent(),
            'date' => Carbon::make($this['created_at'])->diffInMinutes(),
            'location' => $this['user']['location']
        ];
    }
}
