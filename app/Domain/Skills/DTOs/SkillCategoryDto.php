<?php

namespace App\Domain\Skills\DTOs;

use Carbon\Carbon;

class SkillCategoryDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly Carbon $createdAt,
        public readonly Carbon $updatedAt,
        public readonly ?Carbon $deletedAt,
    ) {}

    public static function fromModel(\App\Domain\Skills\Models\SkillCategory $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            description: $model->description,
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
            'description' => $this->description,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
