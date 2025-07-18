<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Financial\Enums\VatRateType;
use App\Domain\Financial\Models\VatRate;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $id   ULID
 * @property string      $name
 * @property float       $rate
 * @property VatRateType $type
 */
final class VatRateDTO extends BaseDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public float $rate,
        public VatRateType $type,
    ) {
    }

    /**
     * @param VatRate $model
     */
    public static function fromModel(Model $model): static
    {
        return new self(
            id: $model->id,
            name: $model->name,
            rate: $model->rate,
            type: $model->type,
        );
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'rate' => $this->rate,
            'type' => $this->type->value,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            name: $data['name'],
            rate: $data['rate'],
            type: VatRateType::from($data['type']),
        );
    }
}
