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
        public ?string $id = null,
        public string $tenantId,
        public string $name,
        public ?string $description = null,
        public string $unitId,
        public float $priceNet,
        public string $vatRateId,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $deletedAt = null,
    ) {}
}
