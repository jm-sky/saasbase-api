<?php

namespace App\Domain\Auth\DTOs;

use App\Domain\Auth\Models\User;
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

    public static function fromModel(User $model): self
    {
        return new self(
            name: $model->name,
            email: $model->email,
            id: $model->id,
            avatarUrl: $model->avatar_url,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
        );
    }
}
