<?php

namespace App\Domain\Products\DTOs;

use App\Domain\Common\Models\Unit;
use App\Domain\Products\Models\Product;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $name
 * @property string $description
 * @property string $sku
 * @property float $price
 * @property int $quantity
 * @property string $unitId
 * @property ?Unit $unit
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt Internally Carbon, accepts/serializes ISO 8601
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

    public static function fromModel(Product $model): self
    {
        return new self(
            tenantId: $model->tenant_id,
            name: $model->name,
            unitId: $model->unit_id,
            priceNet: $model->price_net,
            vatRateId: $model->vat_rate_id,
            id: $model->id,
            description: $model->description,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }
}
