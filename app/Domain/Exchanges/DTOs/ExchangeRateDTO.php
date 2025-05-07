<?php

namespace App\Domain\Exchanges\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Exchanges\Enums\ExchangeRateSource;
use App\Domain\Exchanges\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<ExchangeRate>
 *
 * @property ?string            $id         UUID
 * @property string             $exchangeId UUID
 * @property Carbon             $date
 * @property float              $rate
 * @property string             $table
 * @property ExchangeRateSource $source
 * @property ?Carbon            $createdAt  Internally Carbon, accepts/serializes ISO 8601
 */
class ExchangeRateDTO extends BaseDTO
{
    public function __construct(
        public readonly string $exchangeId,
        public readonly Carbon $date,
        public readonly float $rate,
        public readonly string $table,
        public readonly ExchangeRateSource $source,
        public readonly ?string $id = null,
        public ?Carbon $createdAt = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var ExchangeRate $model */
        return new static(
            exchangeId: $model->exchange_id,
            date: $model->date,
            rate: $model->rate,
            table: $model->table,
            source: $model->source,
            id: $model->id,
            createdAt: $model->created_at,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            exchangeId: $data['exchange_id'],
            date: Carbon::parse($data['date']),
            rate: (float) $data['rate'],
            table: $data['table'],
            source: ExchangeRateSource::from($data['source']),
            id: $data['id'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'exchangeId' => $this->exchangeId,
            'date'       => $this->date->toIso8601String(),
            'rate'       => $this->rate,
            'table'      => $this->table,
            'source'     => $this->source->value,
            'createdAt'  => $this->createdAt?->toIso8601String(),
        ];
    }
}
