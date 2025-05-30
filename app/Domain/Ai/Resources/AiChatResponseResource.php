<?php

namespace App\Domain\Ai\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $id
 * @property string $content
 * @property bool   $streaming
 */
class AiChatResponseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'content'   => $this->content,
            'streaming' => $this->streaming,
        ];
    }
}
