<?php

namespace App\Domain\Products\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Common\Models\MeasurementUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<MeasurementUnit>
 *
 * @property string  $id
 * @property string  $code
 * @property string  $name
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 */
class MeasurementUnitDTO extends BaseDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $id,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var MeasurementUnit $model */
        return new static(
            code: $model->code,
            name: $model->name,
            id: $model->id,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            code: $data['code'],
            name: $data['name'],
            id: $data['id'],
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'code'      => $this->code,
            'name'      => $this->name,
            'createdAt' => $this->createdAt?->toIso8601String(),
            'updatedAt' => $this->updatedAt?->toIso8601String(),
        ];
    }
}
