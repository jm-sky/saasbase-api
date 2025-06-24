<?php

namespace App\Services\NBP\DTOs;

use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class CurrencyRateDTO
{
    public function __construct(
        public string $table,
        public string $currency,
        public string $code,
        public Collection $rates // Collection of rate data
    ) {
    }

    public static function fromArray(array $data): self
    {
        $rates = collect($data['rates'])->map(function (array $rate) {
            return [
                'no'            => $rate['no'],
                'effectiveDate' => Carbon::parse($rate['effectiveDate']),
                'mid'           => (float) $rate['mid'],
            ];
        });

        return new self(
            table: $data['table'],
            currency: $data['currency'],
            code: $data['code'],
            rates: $rates
        );
    }

    public function getLatestRate(): ?array
    {
        return $this->rates->sortByDesc('effectiveDate')->first();
    }

    public function toExchangeRateDTO(): ?ExchangeRateDTO
    {
        $latestRate = $this->getLatestRate();

        if (!$latestRate) {
            return null;
        }

        return new ExchangeRateDTO(
            currencyCode: $this->code,
            currencyName: $this->currency,
            rate: $latestRate['mid'],
            effectiveDate: $latestRate['effectiveDate'],
            table: $this->table,
            no: $latestRate['no']
        );
    }
}
