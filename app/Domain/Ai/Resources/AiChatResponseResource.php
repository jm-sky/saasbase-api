<?php

namespace App\Domain\Ai\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $id
 * @property string $tempId
 * @property string $content
 * @property bool   $streaming
 * @property string $role
 * @property bool   $isAi
 * @property string $createdAt
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
            'createdAt' => $this->createdAt ?? now()->toIso8601String(),
        ];
    }
}
