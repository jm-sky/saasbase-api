<?php

namespace App\Domain\Calendar\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Calendar\Enums\EventVisibility;
use App\Domain\Calendar\Models\Event;

class EventPolicy
{
    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, Event $event): bool
    {
        return EventVisibility::PUBLIC->value === $event->visibility
            || $user->id === $event->created_by_id
            || $event->attendees->contains('attendee_id', $user->id);
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, Event $event): bool
    {
        return $user->id === $event->created_by_id;
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        return $user->id === $event->created_by_id;
    }
}
