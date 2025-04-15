<?php

namespace App\Domain\Auth\DTOs;

use Carbon\Carbon;

class UserDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $avatarUrl,
        public readonly Carbon $createdAt,
        public readonly Carbon $updatedAt,
        public readonly ?Carbon $deletedAt,
    ) {}

    public static function fromModel(\App\Domain\Auth\Models\User $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            avatarUrl: $model->avatar_url,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatarUrl' => $this->avatarUrl,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
