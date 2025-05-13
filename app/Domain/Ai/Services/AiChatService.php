<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\Events\AiChatMessageStreamed;
use Illuminate\Support\Facades\Http;

class AiChatService
{
    public const DEFAULT_OPENROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';

    protected string $openRouterUrl;

    protected string $model;

    protected string $apiKey;

    public function __construct(
        protected AiConversationService $conversationService,
        ?string $openRouterUrl = null
    ) {
        $this->openRouterUrl = $openRouterUrl ?? config('services.openrouter.url', self::DEFAULT_OPENROUTER_URL);

        $this->model  = config('services.openrouter.model', 'openai/gpt-3.5-turbo');
        $this->apiKey = config('services.openrouter.key');
    }

    /**
     * Streams AI response from OpenRouter and broadcasts each chunk.
     */
    public function streamAiResponse(array $history, string $message, string $userId): void
    {
        $messages   = $this->conversationService->buildMessages(collect($history));
        $messages[] = [
            'role'    => 'user',
            'content' => $message,
        ];

        $response = Http::withHeaders($this->getOpenRouterHeaders())
            ->post($this->openRouterUrl, [
                'model'    => $this->model,
                'messages' => $messages,
                'stream'   => true,
            ])
        ;

        $index = 0;

        foreach ($response->stream() as $chunk) {
            if ($this->conversationService->isCancelled()) {
                break;
            }
            $content = $this->parseStreamChunk($chunk);

            if (null !== $content) {
                broadcast(new AiChatMessageStreamed($userId, $content, $index++));
            }
        }
    }

    /**
     * Get headers for OpenRouter API.
     */
    protected function getOpenRouterHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept'        => 'text/event-stream',
        ];
    }

    /**
     * Parses a streamed chunk from OpenRouter.
     */
    protected function parseStreamChunk(string $chunk): ?string
    {
        // OpenRouter streams data as JSON lines prefixed with 'data: '
        foreach (explode("\n", $chunk) as $line) {
            $line = trim($line);

            if (str_starts_with($line, 'data: ')) {
                $json = substr($line, 6);

                if ('[DONE]' === $json) {
                    return null;
                }
                $data = json_decode($json, true);

                if (isset($data['choices'][0]['delta']['content'])) {
                    return $data['choices'][0]['delta']['content'];
                }
            }
        }

        return null;
    }
}
