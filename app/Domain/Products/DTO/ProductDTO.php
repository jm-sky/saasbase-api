<?php

namespace App\Domain\Products\DTO;

use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $tenantId
 * @property string $name
 * @property ?string $description
 * @property string $unitId
 * @property float $priceNet
 * @property string $vatRateId
 * @property ?string $createdAt
 * @property ?string $updatedAt
 * @property ?string $deletedAt
 */
class ProductDTO extends Data
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $name,
        public readonly string $unitId,
        public readonly float $priceNet,
        public readonly string $vatRateId,
        public readonly ?string $id = null,
        public readonly ?string $description = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
        public readonly ?string $deletedAt = null,
    ) {}
}
