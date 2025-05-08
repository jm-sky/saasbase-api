<?php

namespace App\Domain\Chat\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Chat\DTOs\ChatRoomDTO;
use App\Domain\Chat\Models\ChatRoom;
use App\Domain\Chat\Requests\CreateDirectMessageRoomRequest;
use App\Domain\Chat\Services\DirectMessageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DirectMessageController extends Controller
{
    public const ALLOW_SELF_MESSAGE = true;

    public function __construct(
        protected DirectMessageService $dmService
    ) {
    }

    /**
     * Create or get a direct message room for the current user and another user.
     */
    public function createRoom(CreateDirectMessageRoomRequest $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();
        $tenantId    = $currentUser->getTenantId();
        $otherUser   = User::findOrFail($request->getUserId());

        if ($otherUser->id === $currentUser->id && !self::ALLOW_SELF_MESSAGE) {
            return response()->json(['message' => 'Cannot create a direct message room with yourself.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $room = $this->dmService->findOrCreateRoom($tenantId, $currentUser, $otherUser);
        $dto  = ChatRoomDTO::fromModel($room);

        return response()->json([
            'data' => $dto,
        ]);
    }

    /**
     * List all direct message rooms for the current user.
     */
    public function listRooms(Request $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser   = $request->user();
        $tenantId      = $currentUser->getTenantId();

        $rooms = ChatRoom::where('tenant_id', $tenantId)
            ->where('type', 'direct')
            ->whereHas('participants', function ($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id);
            })
            ->get()
        ;

        $rooms = ChatRoomDTO::collect($rooms);

        return response()->json([
            'data' => $rooms,
        ]);
    }
}
