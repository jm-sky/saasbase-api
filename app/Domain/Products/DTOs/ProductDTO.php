<?php

namespace App\Domain\Products\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Common\Models\MeasurementUnit;
use App\Domain\Products\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Product>
 *
 * @property ?string          $id          UUID
 * @property string           $tenantId    UUID
 * @property string           $name
 * @property ?string          $description
 * @property float            $priceNet
 * @property string           $unitId
 * @property string           $vatRateId
 * @property ?MeasurementUnit $unit
 * @property ?Carbon          $createdAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon          $updatedAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon          $deletedAt   Internally Carbon, accepts/serializes ISO 8601
 */
class ProductDTO extends BaseDTO
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $name,
        public readonly string $unitId,
        public readonly float $priceNet,
        public readonly string $vatRateId,
        public readonly ?string $id = null,
        public readonly ?string $description = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            tenantId: $data['tenant_id'],
            name: $data['name'],
            unitId: $data['unit_id'],
            priceNet: $data['price_net'],
            vatRateId: $data['vat_rate_id'],
            id: $data['id'],
            description: $data['description'],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
            deletedAt: $data['deleted_at'],
        );
    }

    public static function fromModel(Model $model): static
    {
        if (!$model instanceof Product) {
            throw new \InvalidArgumentException('Model must be instance of Product');
        }

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

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'tenantId'    => $this->tenantId,
            'name'        => $this->name,
            'description' => $this->description,
            'priceNet'    => $this->priceNet,
            'unitId'      => $this->unitId,
            'vatRateId'   => $this->vatRateId,
            'createdAt'   => $this->createdAt?->toIso8601String(),
            'updatedAt'   => $this->updatedAt?->toIso8601String(),
            'deletedAt'   => $this->deletedAt?->toIso8601String(),
        ];
    }
}
