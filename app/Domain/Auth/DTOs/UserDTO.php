<?php

namespace App\Domain\Auth\DTOs;

use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $name
 * @property string $email
 * @property ?string $avatarUrl
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class UserDTO extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $id = null,
        public readonly ?string $avatarUrl = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}
}
