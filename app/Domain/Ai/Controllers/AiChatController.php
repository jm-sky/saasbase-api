<?php

namespace App\Domain\Ai\Controllers;

use App\Domain\Ai\Requests\AiChatRequest;
use App\Domain\Ai\Resources\AiChatResponseResource;
use App\Domain\Ai\Services\AiChatService;
use App\Domain\Ai\Services\AiConversationService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function chat(AiChatRequest $request): AiChatResponseResource
    {
        $user = $request->user();
        $data = $request->validated();

        $conversationService = new AiConversationService($user->id, $data['threadId'] ?? null, $user->tenant_id ?? null);
        $chatService         = new AiChatService($conversationService);

        if (AiChatService::isStreamingEnabled()) {
            // Streamowanie - zwracamy natychmiast potwierdzenie, dalsza praca dzieje się w tle (broadcast)
            $chatService->streamAiResponse($data['history'] ?? [], $data['message'], $user->id);

            return new AiChatResponseResource((object) [
                'id'        => Str::uuid(),
                'content'   => 'AI response streaming started',
                'streaming' => true,
            ]);
        }

        // Tryb bez streamu - zwracamy odpowiedź od razu do klienta
        $content = $chatService->getFullResponse($data['history'] ?? [], $data['message']);

        return new AiChatResponseResource((object) [
            'id'        => Str::uuid(),
            'content'   => $content,
            'streaming' => false,
        ]);
    }
}
