<?php

namespace App\Domain\Chat\DTOs;

use App\Domain\Chat\Models\ChatRoom;
use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Users\DTOs\UserPreviewDTO;
use Illuminate\Database\Eloquent\Model;

class ChatRoomDTO extends BaseDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public array $participants,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        if (!$model instanceof ChatRoom) {
            throw new \InvalidArgumentException('Model must be instance of ChatRoom');
        }

        return new static(
            $model->id,
            $model->name,
            $model->type,
            participants: $model->participants->map(fn ($participant) => UserPreviewDTO::fromModel($participant->user))->all(),
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            $data['id'],
            $data['name'],
            $data['type'],
            $data['participants'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'type'         => $this->type,
            'participants' => $this->participants,
        ];
    }
}
