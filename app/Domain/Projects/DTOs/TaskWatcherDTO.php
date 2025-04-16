<?php

namespace App\Domain\Projects\DTOs;

use App\Domain\Auth\DTOs\UserDTO;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $taskId
 * @property string $userId
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 * @property ?UserDTO $user
 */
class TaskWatcherDTO extends Data
{
    public function __construct(
        public readonly string $taskId,
        public readonly string $userId,
        public readonly ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
        public ?UserDTO $user = null,
    ) {}
}
