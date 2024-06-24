<?php

namespace App\Http\Resources\Notifications;

use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardNotifications extends JsonResource
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
            'user' => $this['user'],
            'type' => $service->getType(),
            'title' => $service->getTitle(),
            'content' => $service->getContent(),
            'date' => Carbon::make($this['created_at'])->diffForHumans(),
        ];
    }
}
