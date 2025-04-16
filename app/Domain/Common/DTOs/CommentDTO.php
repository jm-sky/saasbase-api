<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $userId
 * @property string $content
 * @property string $commentableId
 * @property string $commentableType
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 * @property ?UserDTO $user
 */
class CommentDTO extends Data
{
    public function __construct(
        public readonly string $userId,
        public readonly string $content,
        public readonly string $commentableId,
        public readonly string $commentableType,
        public readonly ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
        public ?UserDTO $user = null,
    ) {}
}
