<?php

namespace App\Domain\Skills\DTOs;

use App\Domain\Skills\Models\Skill;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapOutputName;

/**
 * @property ?string $id UUID
 * @property string $category
 * @property string $name
 * @property ?string $description
 * @property ?SkillCategoryDTO $skillCategory
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt Internally Carbon, accepts/serializes ISO 8601
 */
class SkillDTO extends Data
{
    public function __construct(
        public readonly string $category,
        public readonly string $name,
        public readonly ?string $id = null,
        public readonly ?string $description = null,
        #[MapOutputName('skill_category')]
        public readonly ?SkillCategoryDTO $skillCategory = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $deletedAt = null,
    ) {}

    public static function fromModel(Skill $model, bool $withRelations = false): self
    {
        return new self(
            category: $model->category,
            name: $model->name,
            id: $model->id,
            description: $model->description,
            skillCategory: $withRelations && $model->relationLoaded('skillCategory')
                ? SkillCategoryDTO::fromModel($model->skillCategory)
                : null,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }
}
