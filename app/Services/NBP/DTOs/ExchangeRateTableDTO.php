<?php

namespace App\Services\NBP\DTOs;

use App\Services\NBP\Enums\NBPTableEnum;
use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class ExchangeRateTableDTO
{
    /**
     * @param Collection<int, ExchangeRateDTO> $rates
     */
    public function __construct(
        public NBPTableEnum $table,
        public string $no,
        public Carbon $effectiveDate,
        public Collection $rates
    ) {}

    public static function fromArray(array $data): self
    {
        /** @var Collection<int, ExchangeRateDTO> $rates */
        $rates = collect($data['rates'])->map(
            fn(array $rate) => ExchangeRateDTO::fromArray(
                $rate,
                $data['effectiveDate'],
                $data['table'],
                $data['no']
            )
        );

        return new self(
            table: NBPTableEnum::fromString($data['table']),
            no: $data['no'],
            effectiveDate: Carbon::parse($data['effectiveDate']),
            rates: $rates
        );
    }

    public function getRateForCurrency(string $currencyCode): ?ExchangeRateDTO
    {
        return $this->rates->first(
            fn(ExchangeRateDTO $rate) => $rate->currencyCode === strtoupper($currencyCode)
        );
    }
}
