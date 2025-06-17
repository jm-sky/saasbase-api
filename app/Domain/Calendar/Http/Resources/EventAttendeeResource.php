<?php

namespace App\Domain\Calendar\Http\Resources;

use App\Domain\Calendar\Models\EventAttendee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventAttendee
 */
class EventAttendeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'eventId'        => $this->event_id,
            'attendeeType'   => $this->attendee_type,
            'attendeeId'     => $this->attendee_id,
            'responseStatus' => $this->response_status,
            'responseAt'     => $this->response_at,
            'customNote'     => $this->custom_note,
            'createdAt'      => $this->created_at,
            'updatedAt'      => $this->updated_at,
            'attendee'       => $this->whenLoaded('attendee'),
        ];
    }
}
