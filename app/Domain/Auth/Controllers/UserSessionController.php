<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Resources\UserSessionResource;
use App\Domain\Auth\Services\UserSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class UserSessionController
{
    public function __construct(
        private readonly UserSessionService $userSessionService,
    ) {
    }

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

    /**
     * Revoke a specific session belonging to the authenticated user.
     */
    public function revoke(Request $request, string $id): JsonResponse
    {
        if (!$this->userSessionService->revokeById($request->user(), $id)) {
            return response()->json(['message' => 'Session not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['message' => 'Session revoked.']);
    }

    /**
     * Revoke every session for the authenticated user except the current one.
     */
    public function revokeOthers(Request $request): JsonResponse
    {
        $count = $this->userSessionService->revokeAllExcept($request->user());

        return response()->json(['message' => 'Other sessions revoked.', 'count' => $count]);
    }
}
