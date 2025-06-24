<?php

namespace App\Services\NBP\DTOs;

use Carbon\Carbon;

readonly class ExchangeRateDTO
{
    public function __construct(
        public string $currencyCode,
        public string $currencyName,
        public float $rate,
        public Carbon $effectiveDate,
        public string $table,
        public string $no
    ) {
    }

    public static function fromArray(array $data, string $effectiveDate, string $table, string $no): self
    {
        return new self(
            currencyCode: $data['code'],
            currencyName: $data['currency'],
            rate: (float) $data['mid'],
            effectiveDate: Carbon::parse($effectiveDate),
            table: $table,
            no: $no
        );
    }

    public function toArray(): array
    {
        return [
            'currency'       => $this->currencyCode,
            'currency_name'  => $this->currencyName,
            'rate'           => $this->rate,
            'date'           => $this->effectiveDate->format('Y-m-d'),
            'table'          => $this->table,
            'no'             => $this->no,
        ];
    }

    public function toModel(): array
    {
        return [
            'currency'       => $this->currencyCode,
            'currency_name'  => $this->currencyName,
            'rate'           => $this->rate,
            'date'           => $this->effectiveDate, // TODO: We want just "date" maybe
            'table'          => $this->table,
            'no'             => $this->no,
        ];
    }
}
