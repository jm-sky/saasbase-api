<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\DTOs\OpenRouterDeltaData;
use App\Domain\Ai\DTOs\OpenRouterStreamChunkData;
use App\Domain\Ai\DTOs\StreamDeltaData;
use App\Domain\Ai\Events\AiChatMessageStreamed;
use App\Domain\Chat\Models\ChatMessage;
use App\Domain\Chat\Models\ChatRoom;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    public const DONE_TOKEN = '[DONE]';

    public const DEFAULT_MODEL = 'openai/gpt-3.5-turbo';

    protected const MAX_BUFFER_LENGTH = 10240; // 10 KB jako bezpieczny limit

    protected string $model;

    protected bool $shouldLog;

    protected OpenRouterService $openRouterService;

    public function __construct(
        protected AiConversationService $conversationService,
        ?string $model = null,
        ?OpenRouterService $openRouterService = null
    ) {
        $this->model             = $model ?? config('services.openrouter.model', self::DEFAULT_MODEL);
        $this->shouldLog         = config('services.openrouter.log', app()->environment('local'));
        $this->openRouterService = $openRouterService ?? new OpenRouterService(model: $this->model);
    }

    public static function isStreamingEnabled(): bool
    {
        return config('services.openrouter.streaming_enabled', true);
    }

    public function streamAiResponse(array $history, string $message, string $userId, ?string $tempId = null, bool $noHistory = false): void
    {
        $messages = $this->buildMessages($history, $message);

        try {
            if (!self::isStreamingEnabled()) {
                $response = $this->openRouterService->createNonStreamedResponse($messages);
                $content  = $this->extractFullContent($response);

                if (!$noHistory) {
                    $this->saveMessages($userId, $message, $content, $tempId);
                }

                $this->broadcastStreamedResponse($userId, $content);

                return;
            }

            $response = $this->openRouterService->createStreamedResponse($messages);
            $this->processStreamedResponse($response, $userId, $message, $tempId, $noHistory);
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

    protected function saveMessages(string $userId, string $userMessage, string $aiResponse, ?string $tempId = null): void
    {
        $room = $this->getOrCreateAiChatRoom($userId);

        // Save user message
        $userMessageModel = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id'      => $userId,
            'content'      => $userMessage,
            'role'         => 'user',
            'is_ai'        => false,
            'temp_id'      => $tempId,
        ]);

        // Save AI response
        ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id'      => $userId,
            'content'      => $aiResponse,
            'role'         => 'assistant',
            'is_ai'        => true,
            'parent_id'    => $userMessageModel->id,
        ]);
    }

    protected function getOrCreateAiChatRoom(string $userId): ChatRoom
    {
        $room = ChatRoom::where('type', 'ai')
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first()
        ;

        if (!$room) {
            $room = ChatRoom::create([
                'name'        => 'AI Chat',
                'type'        => 'ai',
                'description' => 'AI Assistant Chat Room',
            ]);

            $room->participants()->create([
                'user_id'      => $userId,
                'role'         => 'member',
                'joined_at'    => now(),
                'last_read_at' => now(),
            ]);
        }

        return $room;
    }

    protected function broadcastStreamedResponse(string $userId, string $content): void
    {
        broadcast(new AiChatMessageStreamed($userId, new StreamDeltaData(
            id: uniqid('static_', true),
            index: 0,
            provider: 'openrouter',
            model: $this->model,
            content: $content,
        )));
    }

    protected function broadcastStreamedChunk(OpenRouterStreamChunkData $chunk, OpenRouterDeltaData $delta, string $userId, int $index = 0): void
    {
        $deltaDto = $this->buildStreamDeltaData($chunk, $delta, ++$index);
        broadcast(new AiChatMessageStreamed($userId, $deltaDto, $index));

        if ($this->shouldLog) {
            Log::debug("Streamed AI chunk #{$index}: {$delta->content}");
        }
    }

    protected function buildMessages(array $history, string $message): array
    {
        $messages   = $this->conversationService->buildMessages(collect($history));
        $messages[] = ['role' => 'user', 'content' => $message];

        if ($this->shouldLog) {
            Log::debug('Built AI messages', $messages);
        }

        return $messages;
    }

    protected function extractFullContent($response): string
    {
        $json = json_decode($response->getBody()->getContents(), true);

        return $json['choices'][0]['message']['content'] ?? '[empty]';
    }

    protected function processStreamedResponse($response, string $userId, string $userMessage, ?string $tempId = null, bool $noHistory = false): void
    {
        $body        = $response->getBody();
        $index       = 0;
        $buffer      = '';
        $fullContent = '';

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
                        if (!$noHistory) {
                            $this->saveMessages($userId, $userMessage, $fullContent, $tempId);
                        }
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
                        $fullContent .= $delta->content;
                        $this->broadcastStreamedChunk($data, $delta, $userId, $index);
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

    public function getFullResponse(array $history, string $message, ?string $tempId = null, bool $noHistory = false): string
    {
        $messages = $this->buildMessages($history, $message);

        try {
            $response = $this->openRouterService->createNonStreamedResponse($messages);
            $content  = $this->extractFullContent($response);

            if (!$noHistory) {
                $this->saveMessages($this->conversationService->getUserId(), $message, $content, $tempId);
            }

            return $content;
        } catch (\Throwable $e) {
            Log::error('AI request failed (non-streamed): ' . $e->getMessage());

            return 'Error: AI service unavailable';
        }
    }
}
