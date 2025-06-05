<?php

namespace App\Domain\Ai\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AiConversationService
{
    public function __construct(
        protected readonly string $userId,
        protected readonly ?string $threadId = null,
        protected readonly ?string $tenantId = null,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function generateSystemPrompt(): string
    {
        return "You are an AI assistant for user #{$this->userId}."
            . ' Never reference other users or external information.'
            . ' Only use context from this chat.';
    }

    public function buildMessages(Collection $history): array
    {
        $messages = [
            [
                'role'    => 'system',
                'content' => $this->generateSystemPrompt(),
            ],
        ];

        foreach ($history as $msg) {
            $messages[] = [
                'role'    => 'ai' === $msg['sender'] ? 'assistant' : 'user',
                'content' => $msg['content'],
            ];
        }

        return $messages;
    }

    public function cacheKey(): string
    {
        $key = "ai:user:{$this->userId}";

        if ($this->threadId) {
            $key .= ":thread:{$this->threadId}";
        }

        if ($this->tenantId) {
            $key .= ":tenant:{$this->tenantId}";
        }

        return $key;
    }

    public function storeLastJobId(string $jobId): void
    {
        Cache::put($this->cacheKey() . ':job_id', $jobId, now()->addMinutes(5));
    }

    public function markCancelled(): void
    {
        Cache::put($this->cacheKey() . ':cancelled', true, now()->addMinutes(5));
    }

    public function isCancelled(): bool
    {
        return Cache::get($this->cacheKey() . ':cancelled', false);
    }

    public function getLastJobId(): ?string
    {
        return Cache::get($this->cacheKey() . ':job_id');
    }

    public function clearState(): void
    {
        Cache::forget($this->cacheKey() . ':job_id');
        Cache::forget($this->cacheKey() . ':cancelled');
    }
}
