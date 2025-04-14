<?php

namespace App\Domain\Skills\DTOs;

use Carbon\Carbon;

class SkillCategoryDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?Carbon $deleted_at,
    ) {}

    public static function fromModel(\App\Domain\Skills\Models\SkillCategory $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
            deleted_at: $model->deleted_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
