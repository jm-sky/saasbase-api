<?php

namespace App\Domain\Products\DTOs;

use Carbon\Carbon;

class ProductDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenant_id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $unit_id,
        public readonly float $price_net,
        public readonly string $vat_rate_id,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?Carbon $deleted_at,
        public readonly ?UnitDto $unit = null,
        public readonly ?VatRateDto $vatRate = null,
    ) {}

    public static function fromModel(\App\Domain\Products\Models\Product $model, bool $withRelations = false): self
    {
        return new self(
            id: $model->id,
            tenant_id: $model->tenant_id,
            name: $model->name,
            description: $model->description,
            unit_id: $model->unit_id,
            price_net: $model->price_net,
            vat_rate_id: $model->vat_rate_id,
            created_at: $model->created_at,
            updated_at: $model->updated_at,
            deleted_at: $model->deleted_at,
            unit: $withRelations && $model->relationLoaded('unit')
                ? UnitDto::fromModel($model->unit)
                : null,
            vatRate: $withRelations && $model->relationLoaded('vatRate')
                ? VatRateDto::fromModel($model->vatRate)
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'description' => $this->description,
            'unit_id' => $this->unit_id,
            'price_net' => $this->price_net,
            'vat_rate_id' => $this->vat_rate_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'unit' => $this->unit?->toArray(),
            'vat_rate' => $this->vatRate?->toArray(),
        ];
    }
}
