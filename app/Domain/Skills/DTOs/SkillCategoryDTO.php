<?php

namespace App\Domain\Skills\DTOs;

use Spatie\LaravelData\Data;
use App\Domain\Skills\Models\SkillCategory;

/**
 * @property ?string $id UUID
 * @property string $name
 * @property ?string $description
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class SkillCategoryDTO extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $id = null,
        public ?string $description = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}

    public static function fromModel(SkillCategory $model): self
    {
        if (!$model->name) {
            throw new \InvalidArgumentException('SkillCategory name is required');
        }

        return new self(
            name: $model->name,
            id: $model->id,
            description: $model->description,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
        );
    }
}
