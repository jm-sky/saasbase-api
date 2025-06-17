<?php

namespace App\Domain\Ai\Controllers;

use App\Domain\Ai\DTOs\AiChatResponseDTO;
use App\Domain\Ai\Requests\AiChatRequest;
use App\Domain\Ai\Resources\AiChatResponseResource;
use App\Domain\Ai\Services\AiChatService;
use App\Domain\Ai\Services\AiConversationService;
use App\Domain\Auth\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function chat(AiChatRequest $request): AiChatResponseResource
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validated();

        $conversationService = new AiConversationService($user->id, $data['threadId'] ?? null, $user->getTenantId() ?? null);
        $chatService         = new AiChatService($conversationService);

        if (AiChatService::isStreamingEnabled()) {
            // Streamowanie - zwracamy natychmiast potwierdzenie, dalsza praca dzieje się w tle (broadcast)
            $chatService->streamAiResponse(
                $data['history'] ?? [],
                $data['message'],
                $user->id,
                $data['tempId'] ?? null,
                $data['noHistory'] ?? false
            );

            return new AiChatResponseResource(new AiChatResponseDTO(
                id: Str::ulid(),
                tempId: $data['tempId'] ?? null,
                content: 'AI response streaming started',
                streaming: true,
                role: 'assistant',
                isAi: true,
                createdAt: now(),
            ));
        }

        // Tryb bez streamu - zwracamy odpowiedź od razu do klienta
        $content = $chatService->getFullResponse(
            $data['history'] ?? [],
            $data['message'],
            $data['tempId'] ?? null,
            $data['noHistory'] ?? false
        );

        return new AiChatResponseResource(new AiChatResponseDTO(
            id: Str::ulid(),
            tempId: $data['tempId'] ?? null,
            content: $content,
            streaming: false,
            role: 'assistant',
            isAi: true,
            createdAt: now(),
        ));
    }

    public function stopStreaming(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $conversationService = new AiConversationService(
            $user->id,
            request()->get('threadId'),
            $user->getTenantId() ?? null
        );

        $conversationService->markCancelled();

        return response()->json([
            'success' => true,
            'message' => 'Streaming cancelled successfully',
        ]);
    }
}
