<?php

namespace App\Domain\Skills\DTOs;

use App\Domain\Skills\Models\SkillCategory;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id          UUID
 * @property string  $name
 * @property ?string $description
 * @property ?Carbon $createdAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt   Internally Carbon, accepts/serializes ISO 8601
 */
class SkillCategoryDTO extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $id = null,
        public ?string $description = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $deletedAt = null,
    ) {
    }

    public static function fromModel(SkillCategory $model): self
    {
        if (!$model->name) {
            throw new \InvalidArgumentException('SkillCategory name is required');
        }

        return new self(
            name: $model->name,
            id: $model->id,
            description: $model->description,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }
}
