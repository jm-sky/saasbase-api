<?php

namespace App\Domain\Auth\Controllers;

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

        return UserSessionResource::collection($sessions);
    }
}
