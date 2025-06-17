<?php

namespace App\Domain\Calendar\Http\Resources;

use App\Domain\Calendar\Models\Event;
use App\Domain\Common\Resources\UserPreviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Event
 */
class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'description'      => $this->description,
            'startAt'          => $this->start_at,
            'endAt'            => $this->end_at,
            'isAllDay'         => $this->is_all_day,
            'location'         => $this->location,
            'color'            => $this->color,
            'status'           => $this->status,
            'visibility'       => $this->visibility,
            'timezone'         => $this->timezone,
            'recurrenceRule'   => $this->recurrence_rule,
            'reminderSettings' => $this->reminder_settings,
            'createdById'      => $this->created_by_id,
            'relatedType'      => $this->related_type,
            'relatedId'        => $this->related_id,
            'createdAt'        => $this->created_at,
            'updatedAt'        => $this->updated_at,
            'creator'          => new UserPreviewResource($this->whenLoaded('creator')),
            'attendees'        => EventAttendeeResource::collection($this->whenLoaded('attendees')),
            'reminders'        => EventReminderResource::collection($this->whenLoaded('reminders')),
        ];
    }
}
