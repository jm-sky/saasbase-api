<?php

namespace App\Domain\Products\DTOs;

use App\Domain\Products\Models\VatRate;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $name
 * @property float $rate
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class VatRateDTO extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly float $rate,
        public readonly ?string $id = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}

    public static function fromModel(VatRate $model): self
    {
        return new self(
            name: $model->name,
            rate: $model->rate,
            id: $model->id,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
        );
    }
}
