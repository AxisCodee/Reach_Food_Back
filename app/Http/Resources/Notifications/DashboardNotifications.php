<?php

namespace App\Http\Resources\Notifications;

use App\Enums\NotificationActions;
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
        $service = new NotificationService($this->resource);
        $date = Carbon::make($this['updated_at'])->locale('ar');
        return [
            'id' => $this['id'],
            'user' => $this['user'],
            'type' => $service->getType(),
            'title' => $service->getTitle(),
            'content' => $service->getContent(),
            'date' => $date->diffForHumans(),
            'there_back' => $this['action_type'] == NotificationActions::DELETE->value,
        ];
    }
}
