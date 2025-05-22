<?php

namespace App\Domain\Calendar\Http\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Calendar\Http\Requests\StoreEventRequest;
use App\Domain\Calendar\Http\Requests\UpdateEventRequest;
use App\Domain\Calendar\Http\Resources\EventResource;
use App\Domain\Calendar\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = Auth::user();

        $events = Event::with(['creator', 'attendees', 'reminders'])
            ->where('tenant_id', $user->getTenantId())
            ->get()
        ;

        return EventResource::collection($events);
    }

    public function store(StoreEventRequest $request): EventResource
    {
        /** @var User $user */
        $user = Auth::user();

        $event = Event::create([
            ...$request->validated(),
            'tenant_id'     => $user->getTenantId(),
            'created_by_id' => $user->id,
        ]);

        if ($request->has('attendees')) {
            $event->attendees()->createMany($request->attendees);
        }

        return new EventResource($event->load(['creator', 'attendees', 'reminders']));
    }

    public function show(Event $event): EventResource
    {
        return new EventResource($event->load(['creator', 'attendees', 'reminders']));
    }

    public function update(UpdateEventRequest $request, Event $event): EventResource
    {
        $event->update($request->validated());

        return new EventResource($event->load(['creator', 'attendees', 'reminders']));
    }

    public function destroy(Event $event): Response
    {
        $event->delete();

        return response()->noContent();
    }
}
