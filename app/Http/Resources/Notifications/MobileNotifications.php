<?php

namespace App\Http\Resources\Notifications;

use App\Models\Notification;
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
        $service = new NotificationService($this->resource);
        $date = Carbon::make($this['updated_at']);
        return [
            'title' => $service->getTitle(),
            'content' => $service->getContent(auth()->id()),
            'date' => $this->getDate($date),
            'image' => $this['user']?->image,
            'location' => $this['user']?->location,
            'is_read' => (bool)$this['pivot']['read'],
        ];
    }

    public function getDate(Carbon $date): string
    {
        if ($date->toDateString() === Carbon::today()->toDateString()){
            return "اليوم: {$date->format('g:i A')}";
        }
        if ($date->toDateString() === Carbon::today()->subDay()->toDateString()){
            return "الأمس: {$date->format('g:i A')}";
        }
        return $date->toDateString();
    }
}
