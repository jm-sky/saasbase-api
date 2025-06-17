<?php

namespace App\Domain\Ai\Resources;

use App\Domain\Ai\DTOs\AiChatResponseDTO;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AiChatResponseDTO
 */
class AiChatResponseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'tempId'    => $this->tempId,
            'content'   => $this->content,
            'streaming' => $this->streaming,
            'role'      => $this->role ?? 'assistant',
            'isAi'      => $this->isAi ?? true,
            'createdAt' => $this->createdAt?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }
}
