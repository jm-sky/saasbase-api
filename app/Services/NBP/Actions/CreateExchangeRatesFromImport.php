<?php

namespace App\Services\NBP\Actions;

use App\Domain\Exchanges\Enums\ExchangeRateSource;
use App\Domain\Exchanges\Models\Currency;
use App\Domain\Exchanges\Models\ExchangeRate;
use App\Services\NBP\DTOs\ExchangeRateDTO;
use App\Services\NBP\NBPService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CreateExchangeRatesFromImport
{
    public static function handle(Collection $rates, bool $force = false, int &$imported = 0, int &$skipped = 0): int
    {
        DB::transaction(function () use ($rates, $force, &$imported, &$skipped) {
            /** @var ExchangeRateDTO $rateDTO */
            foreach ($rates as $rateDTO) {
                Currency::firstOrCreate([
                    'code'   => $rateDTO->currencyCode,
                ], [
                    'name'   => $rateDTO->currencyName,
                    'symbol' => $rateDTO->currencySymbol ?? null,
                ]);

                $exists = ExchangeRate::where([
                    'base_currency'  => NBPService::BASE_CURRENCY,
                    'currency'       => $rateDTO->currencyCode,
                    'date'           => $rateDTO->effectiveDate->format('Y-m-d'),
                    'table'          => $rateDTO->table,
                ])->exists();

                if ($exists && !$force) {
                    ++$skipped;
                    continue;
                }

                ExchangeRate::updateOrCreate(
                    [
                        'base_currency'  => NBPService::BASE_CURRENCY,
                        'currency'       => $rateDTO->currencyCode,
                        'date'           => $rateDTO->effectiveDate->format('Y-m-d'),
                        'table'          => $rateDTO->table,
                        'source'         => ExchangeRateSource::NBP,
                        'no'             => $rateDTO->no,
                    ],
                    $rateDTO->toModel()
                );

                ++$imported;
            }
        });

        return $imported;
    }
}
