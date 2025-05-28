<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\DTOs\OpenRouterDeltaData;
use App\Domain\Ai\DTOs\OpenRouterStreamChunkData;
use App\Domain\Ai\DTOs\StreamDeltaData;
use App\Domain\Ai\Events\AiChatMessageStreamed;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    public const DONE_TOKEN = '[DONE]';

    public const DEFAULT_OPENROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';

    protected const MAX_BUFFER_LENGTH = 10240; // 10 KB jako bezpieczny limit

    protected string $openRouterUrl;

    protected string $model;

    protected string $apiKey;

    protected Client $guzzleClient;

    protected bool $shouldLog;

    public function __construct(
        protected AiConversationService $conversationService,
        ?string $openRouterUrl = null
    ) {
        $this->openRouterUrl = $openRouterUrl ?? config('services.openrouter.url', self::DEFAULT_OPENROUTER_URL);
        $this->model         = config('services.openrouter.model', 'openai/gpt-3.5-turbo');
        $this->apiKey        = config('services.openrouter.key');
        $this->guzzleClient  = new Client();
        $this->shouldLog     = config('services.openrouter.log', app()->environment('local'));
    }

    public function streamAiResponse(array $history, string $message, string $userId): void
    {
        $messages = $this->buildMessages($history, $message);

        try {
            if (!config('services.openrouter.streaming_enabled', true)) {
                $response = $this->createNonStreamedResponse($messages);
                $content  = $this->extractFullContent($response);
                broadcast(new AiChatMessageStreamed($userId, new StreamDeltaData(
                    id: uniqid('static_', true),
                    index: 0,
                    provider: 'openrouter',
                    model: $this->model,
                    content: $content,
                )));

                return;
            }

            $response = $this->createStreamedResponse($messages);
            $this->processStreamedResponse($response, $userId);
        } catch (RequestException $e) {
            Log::error('AI request failed: ' . $e->getMessage(), [
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Unexpected error while streaming AI response', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }

    private function buildMessages(array $history, string $message): array
    {
        $messages   = $this->conversationService->buildMessages(collect($history));
        $messages[] = ['role' => 'user', 'content' => $message];

        if ($this->shouldLog) {
            Log::debug('Built AI messages', $messages);
        }

        return $messages;
    }

    private function createStreamedResponse(array $messages)
    {
        return $this->guzzleClient->post($this->openRouterUrl, [
            'headers' => $this->getOpenRouterHeaders(),
            'json'    => [
                'model'    => $this->model,
                'messages' => $messages,
                'stream'   => true,
            ],
            'stream'          => true,
            'timeout'         => 60,
            'connect_timeout' => 10,
        ]);
    }

    private function createNonStreamedResponse(array $messages)
    {
        return $this->guzzleClient->post($this->openRouterUrl, [
            'headers' => $this->getOpenRouterHeaders(),
            'json'    => [
                'model'    => $this->model,
                'messages' => $messages,
                'stream'   => false,
            ],
            'timeout'         => 60,
            'connect_timeout' => 10,
        ]);
    }

    private function extractFullContent($response): string
    {
        $json = json_decode($response->getBody()->getContents(), true);

        return $json['choices'][0]['message']['content'] ?? '[empty]';
    }

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

            // Zabezpieczenie przed wyciekiem pamięci
            if (strlen($buffer) > self::MAX_BUFFER_LENGTH) {
                $buffer = substr($buffer, -self::MAX_BUFFER_LENGTH);
            }

            while (($pos = strpos($buffer, "\n")) !== false) {
                // Ochrona przed nieskończoną pętlą jeśli \n jest na początku bufora
                if (0 === $pos) {
                    $buffer = substr($buffer, 1);
                    continue;
                }

                $line   = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);
                $line   = trim($line);

                if (str_starts_with($line, 'data: ')) {
                    $json = substr($line, 6);

                    if (self::DONE_TOKEN === $json) {
                        continue;
                    }

                    $json = json_decode($json, true);

                    if (!$json || !isset($json['choices'][0]['delta']['content'])) {
                        Log::warning('Malformed stream chunk', ['line' => $line]);
                        continue;
                    }

                    $data  = OpenRouterStreamChunkData::fromArray($json);
                    $delta = $data->choices[0]->delta;

                    if (isset($delta->content)) {
                        $deltaDto = $this->buildStreamDeltaData($data, $delta, ++$index);
                        broadcast(new AiChatMessageStreamed($userId, $deltaDto, $index));

                        if ($this->shouldLog) {
                            Log::debug("Streamed AI chunk #{$index}: {$delta->content}");
                        }
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

    protected function getOpenRouterHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept'        => 'text/event-stream',
        ];
    }

    public function getFullResponse(array $history, string $message): string
    {
        $messages = $this->buildMessages($history, $message);

        try {
            $response = $this->createNonStreamedResponse($messages);

            return $this->extractFullContent($response);
        } catch (\Throwable $e) {
            Log::error('AI request failed (non-streamed): ' . $e->getMessage());

            return 'Error: AI service unavailable';
        }
    }
}
