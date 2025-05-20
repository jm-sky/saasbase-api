<?php

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20)
        ;
    }

    public function markAsRead(Request $request)
    {
        $request->user()->unreadNotifications()
            ->whereIn('id', $request->input('ids', []))
            ->update(['read_at' => now()])
        ;

        return response()->noContent();
    }

    public function archive(Request $request)
    {
        $request->user()->notifications()
            ->whereIn('id', $request->input('ids', []))
            ->delete() // or move to a separate table
        ;

        return response()->noContent();
    }
}
