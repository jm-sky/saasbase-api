<?php

namespace App\Domain\Exchanges\DTOs;

use App\Domain\Exchanges\Models\Exchange;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id        UUID
 * @property string  $name
 * @property string  $currency
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 */
class ExchangeDTO extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $currency,
        public readonly ?string $id = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $updatedAt = null,
    ) {
    }

    public static function fromModel(Exchange $exchange): self
    {
        return new self(
            name: $exchange->name,
            currency: $exchange->currency,
            id: $exchange->id,
            createdAt: $exchange->created_at,
            updatedAt: $exchange->updated_at,
        );
    }
}
