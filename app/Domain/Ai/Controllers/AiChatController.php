<?php

namespace App\Domain\Ai\Controllers;

use App\Domain\Ai\Requests\AiChatRequest;
use App\Domain\Ai\Services\AiChatService;
use App\Domain\Ai\Services\AiConversationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AiChatController extends Controller
{
    public function chat(AiChatRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $conversationService = new AiConversationService($user->id, $data['threadId'] ?? null, $user->tenant_id ?? null);
        $chatService         = new AiChatService($conversationService);

        if (config('services.openrouter.streaming_enabled', true)) {
            // Streamowanie - zwracamy natychmiast potwierdzenie, dalsza praca dzieje się w tle (broadcast)
            $chatService->streamAiResponse($data['history'] ?? [], $data['message'], $user->id);

            return response()->json([
                'message' => 'AI response streaming started',
            ], Response::HTTP_ACCEPTED);
        }
        // Tryb bez streamu - zwracamy odpowiedź od razu do klienta
        $content = $chatService->getFullResponse($data['history'] ?? [], $data['message']);

        return response()->json([
            'message' => $content,
        ]);
    }
}
