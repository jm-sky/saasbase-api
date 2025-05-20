<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Requests\ArchiveNotificationsRequest;
use App\Domain\Auth\Requests\MarkNotificationsAsReadRequest;
use App\Domain\Auth\Resources\NotificationResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public const NOTIFICATIONS_PER_PAGE = 20;

    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = $request->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(self::NOTIFICATIONS_PER_PAGE)
        ;

        return NotificationResource::collection($notifications);
    }

    public function markAsRead(MarkNotificationsAsReadRequest $request)
    {
        $request->user()->unreadNotifications()
            ->whereIn('id', $request->input('ids'))
            ->update(['read_at' => now()])
        ;

        return response()->noContent();
    }

    public function archive(ArchiveNotificationsRequest $request)
    {
        $request->user()->notifications()
            ->whereIn('id', $request->input('ids'))
            ->delete() // or move to a separate table
        ;

        return response()->noContent();
    }
}
