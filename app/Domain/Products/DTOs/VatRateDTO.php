<?php

namespace App\Domain\Products\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Common\Models\VatRate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<VatRate>
 *
 * @property ?string $id        UUID
 * @property string  $name
 * @property float   $rate
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt Internally Carbon, accepts/serializes ISO 8601
 */
class VatRateDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly float $rate,
        public readonly ?string $id = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var VatRate $model */
        return new static(
            name: $model->name,
            rate: $model->rate,
            id: $model->id,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            rate: (float) $data['rate'],
            id: $data['id'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? Carbon::parse($data['deleted_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'rate'      => $this->rate,
            'createdAt' => $this->createdAt?->toIso8601String(),
            'updatedAt' => $this->updatedAt?->toIso8601String(),
            'deletedAt' => $this->deletedAt?->toIso8601String(),
        ];
    }
}
