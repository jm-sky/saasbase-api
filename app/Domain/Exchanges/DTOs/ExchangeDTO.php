<?php

namespace App\Domain\Exchanges\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Exchanges\Models\Exchange;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Exchange>
 *
 * @property ?string $id        UUID
 * @property string  $name
 * @property string  $currency
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 */
class ExchangeDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $currency,
        public readonly ?string $id = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var Exchange $model */
        return new static(
            name: $model->name,
            currency: $model->currency,
            id: $model->id,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            currency: $data['currency'],
            id: $data['id'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'currency'  => $this->currency,
            'createdAt' => $this->createdAt?->toIso8601String(),
            'updatedAt' => $this->updatedAt?->toIso8601String(),
        ];
    }
}
