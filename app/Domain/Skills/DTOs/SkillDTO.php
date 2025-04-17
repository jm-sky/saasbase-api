<?php

namespace App\Domain\Skills\DTOs;

use App\Domain\Skills\Models\Skill;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $categoryId
 * @property string $name
 * @property ?string $description
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class SkillDTO extends Data
{
    public function __construct(
        public readonly string $categoryId,
        public readonly string $name,
        public readonly ?string $id = null,
        public ?string $description = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}

    public static function fromModel(Skill $model): self
    {
        return new self(
            categoryId: $model->category_id,
            name: $model->name,
            id: $model->id,
            description: $model->description,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
        );
    }
}
