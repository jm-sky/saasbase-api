<?php

namespace App\Domain\Users\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Users\Models\SecurityEvent;
use App\Domain\Users\Resources\SecurityEventResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SecurityEventController
{
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $events = $user->securityEvents()->paginate();

        return response()->json(SecurityEventResource::collection($events));
    }

    public function show(SecurityEvent $event): JsonResponse
    {
        // $this->authorize('view', $event);

        return response()->json(new SecurityEventResource($event));
    }
}
