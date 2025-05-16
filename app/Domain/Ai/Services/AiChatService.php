<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\DTOs\OpenRouterDeltaData;
use App\Domain\Ai\DTOs\OpenRouterStreamChunkData;
use App\Domain\Ai\DTOs\StreamDeltaData;
use App\Domain\Ai\Events\AiChatMessageStreamed;
use GuzzleHttp\Client;

class AiChatService
{
    public const DONE_TOKEN = '[DONE]';

    public const DEFAULT_OPENROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';

    protected string $openRouterUrl;

    protected string $model;

    protected string $apiKey;

    protected Client $guzzleClient;

    public static function getOpenRouterUrl(): string
    {
        return config('services.openrouter.url', self::DEFAULT_OPENROUTER_URL);
    }

    public function __construct(
        protected AiConversationService $conversationService,
        ?string $openRouterUrl = null
    ) {
        $this->openRouterUrl = $openRouterUrl ?? self::getOpenRouterUrl();

        $this->model        = config('services.openrouter.model', 'openai/gpt-3.5-turbo');
        $this->apiKey       = config('services.openrouter.key');
        $this->guzzleClient = new Client();
    }

    /**
     * Streams AI response from OpenRouter and broadcasts each chunk.
     */
    public function streamAiResponse(array $history, string $message, string $userId): void
    {
        $messages = $this->buildMessages($history, $message);
        $response = $this->createStreamedResponse($messages);
        $this->processStreamedResponse($response, $userId);
    }

    /**
     * Build the messages array for the OpenRouter API.
     */
    private function buildMessages(array $history, string $message): array
    {
        $messages   = $this->conversationService->buildMessages(collect($history));
        $messages[] = [
            'role'    => 'user',
            'content' => $message,
        ];

        return $messages;
    }

    /**
     * Create a streamed response from OpenRouter using Guzzle.
     */
    private function createStreamedResponse(array $messages)
    {
        return $this->guzzleClient->post($this->openRouterUrl, [
            'headers' => $this->getOpenRouterHeaders(),
            'json'    => [
                'model'    => $this->model,
                'messages' => $messages,
                'stream'   => true,
            ],
            'stream' => true,
        ]);
    }

    /**
     * Process the streamed response and broadcast each chunk.
     */
    private function processStreamedResponse($response, string $userId): void
    {
        $body   = $response->getBody();
        $index  = 0;
        $buffer = '';

        while (!$body->eof()) {
            if ($this->conversationService->isCancelled()) {
                break;
            }

            $buffer .= $body->read(1024);

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line   = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);

                $line = trim($line);

                if (str_starts_with($line, 'data: ')) {
                    $json = substr($line, 6);

                    if (self::DONE_TOKEN === $json) {
                        continue;
                    }

                    $json             = json_decode($json, true);
                    $data             = $json ? OpenRouterStreamChunkData::fromArray($json) : null;

                    if (isset($data->choices[0]->delta->content) && $data->choices[0]->delta->content) {
                        $delta = $data->choices[0]->delta;
                        ++$index;
                        $deltaDto = $this->buildStreamDeltaData($data, $delta, $index);
                        broadcast(new AiChatMessageStreamed($userId, $deltaDto, $index));
                    }
                }
            }
        }
    }

    protected function buildStreamDeltaData(OpenRouterStreamChunkData $chunk, OpenRouterDeltaData $delta, int $index): StreamDeltaData
    {
        return new StreamDeltaData(
            id: $chunk->id,
            index: $index,
            provider: $chunk->provider,
            model: $chunk->model,
            content: $delta->content,
        );
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
     *
     * @return array<OpenRouterStreamChunkData>
     */
    protected function parseStreamChunk(string $chunk): array
    {
        $contents = [];

        // OpenRouter streams data as JSON lines prefixed with 'data: '
        foreach (explode("\n", $chunk) as $line) {
            $line = trim($line);

            if (str_starts_with($line, 'data: ')) {
                $json = substr($line, 6);

                if (self::DONE_TOKEN === $json) {
                    continue;
                }

                $json = json_decode($json, true);
                $data = $json ? OpenRouterStreamChunkData::fromArray($json) : null;

                if (isset($data->choices[0]->delta->content) && $data->choices[0]->delta->content) {
                    $contents[] = $data;
                }
            }
        }

        return $contents;
    }
}
