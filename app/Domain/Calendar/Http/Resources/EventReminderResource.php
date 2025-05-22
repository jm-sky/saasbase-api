<?php

namespace App\Domain\Calendar\Http\Resources;

use App\Domain\Common\Resources\UserPreviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventReminderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'eventId'      => $this->event_id,
            'userId'       => $this->user_id,
            'reminderAt'   => $this->reminder_at,
            'reminderType' => $this->reminder_type,
            'isSent'       => $this->is_sent,
            'createdAt'    => $this->created_at,
            'updatedAt'    => $this->updated_at,
            'user'         => new UserPreviewResource($this->whenLoaded('user')),
        ];
    }
}
