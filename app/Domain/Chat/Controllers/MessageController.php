<?php

namespace App\Domain\Chat\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Chat\DTOs\ChatMessageDTO;
use App\Domain\Chat\Events\MessageSent;
use App\Domain\Chat\Models\ChatMessage;
use App\Domain\Chat\Models\ChatRoom;
use App\Domain\Chat\Requests\SendMessageRequest;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    public static bool $sendDummyMessages = true;

    public function __construct()
    {
        self::$sendDummyMessages = 'local' === config('app.env');
    }

    /**
     * Send a message in a chat room.
     */
    public function sendMessage(SendMessageRequest $request, ChatRoom $room): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        // Authorization: ensure user is a participant
        if (!$room->isUserParticipant($currentUser->id)) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $data = $request->validated();

        $message = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id'      => $currentUser->id,
            'parent_id'    => $data['parentId'] ?? null,
            'content'      => $data['content'],
            'tenant_id'    => $room->tenant_id,
        ]);

        event(new MessageSent($message));

        if (self::$sendDummyMessages) {
            $this->sendDummyMessage($room, $currentUser->id);
        }

        $dto = ChatMessageDTO::fromModel($message);

        return response()->json([
            'data' => $dto->toArray(),
        ], Response::HTTP_CREATED);
    }

    protected function sendDummyMessage(ChatRoom $room, string $userId): void
    {
        $message = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id'      => $userId,
            'content'      => 'Yes, that\'s right! But what do You think about this? ' . Inspiring::quotes()->random(),
            'tenant_id'    => $room->tenant_id,
        ]);

        event(new MessageSent($message));
    }

    /**
     * List messages in a chat room (optionally paginated).
     */
    public function listMessages(Request $request, ChatRoom $room): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        // Authorization: ensure user is a participant
        if (!$room->participants()->where('user_id', $currentUser->id)->exists()) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $query    = $room->messages()->orderBy('created_at', 'asc');
        $messages = $query->get();

        $dtos = ChatMessageDTO::collect($messages);

        return response()->json([
            'data' => $dtos,
        ]);
    }
}
