<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Enums\SessionType;
use App\Domain\Auth\Models\UserSession;
use App\Domain\Auth\Resources\UserSessionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserSessionController
{
    /**
     * Get all sessions for the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $sessions = $request->user()
            ->sessions()
            ->orderBy('last_active_at', 'desc')
            ->paginate()
        ;

        if (0 === $request->user()->sessions()->count()) {
            $sessions->push(new UserSession([
                'id'             => 'current',
                'type'           => SessionType::JWT,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'last_active_at' => now(),
                'expires_at'     => now()->addMinutes(30),
            ]));
        }

        return UserSessionResource::collection($sessions);
    }
}
