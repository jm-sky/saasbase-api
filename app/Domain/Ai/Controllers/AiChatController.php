<?php

namespace App\Domain\Ai\Controllers;

use Illuminate\Support\Str;
use App\Domain\Auth\Models\User;
use App\Http\Controllers\Controller;
use App\Domain\Ai\Requests\AiChatRequest;
use App\Domain\Ai\Services\AiChatService;
use App\Domain\Ai\Services\AiConversationService;
use App\Domain\Ai\Resources\AiChatResponseResource;

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
