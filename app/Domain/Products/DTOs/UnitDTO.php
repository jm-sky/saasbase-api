<?php

namespace App\Domain\Products\DTOs;

use App\Domain\Products\Models\Unit;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $code
 * @property string $name
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class UnitDTO extends Data
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}

    public static function fromModel(Unit $model): self
    {
        return new self(
            code: $model->code,
            name: $model->name,
            id: $model->id,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
        );
    }
}
