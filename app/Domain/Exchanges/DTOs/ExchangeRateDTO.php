<?php

namespace App\Domain\Exchanges\DTOs;

use App\Domain\Exchanges\Enums\ExchangeRateSource;
use App\Domain\Exchanges\Models\ExchangeRate;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string            $id         UUID
 * @property string             $exchangeId UUID
 * @property Carbon             $date
 * @property float              $rate
 * @property string             $table
 * @property ExchangeRateSource $source
 * @property ?Carbon            $createdAt  Internally Carbon, accepts/serializes ISO 8601
 */
class ExchangeRateDTO extends Data
{
    public function __construct(
        public readonly string $exchangeId,
        #[WithCast(DateTimeInterfaceCast::class)]
        public readonly Carbon $date,
        public readonly float $rate,
        public readonly string $table,
        #[WithCast(EnumCast::class)]
        public readonly ExchangeRateSource $source,
        public readonly ?string $id = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $createdAt = null,
    ) {
    }

    public static function fromModel(ExchangeRate $rate): self
    {
        return new self(
            exchangeId: $rate->exchange_id,
            date: $rate->date,
            rate: $rate->rate,
            table: $rate->table,
            source: $rate->source,
            id: $rate->id,
            createdAt: $rate->created_at,
        );
    }
}
