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
final class ExchangeRateDTO extends BaseDTO
{
    public function __construct(
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
        return new self(
            date: $model->date,
            rate: $model->rate,
            table: $model->table,
            source: ExchangeRateSource::from($model->source),
            id: $model->id,
            createdAt: $model->created_at,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'] ?? null,
            date: Carbon::parse($data['date']),
            rate: (float) $data['rate'],
            table: $data['table'],
            source: ExchangeRateSource::from($data['source']),
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'date'       => $this->date->toIso8601String(),
            'rate'       => $this->rate,
            'table'      => $this->table,
            'source'     => $this->source->value,
            'createdAt'  => $this->createdAt?->toIso8601String(),
        ];
    }
}
