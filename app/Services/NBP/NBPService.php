<?php

namespace App\Services\NBP;

use App\Services\NBP\DTOs\CurrencyRateDTO;
use App\Services\NBP\DTOs\ExchangeRateDTO;
use App\Services\NBP\DTOs\ExchangeRateTableDTO;
use App\Services\NBP\Requests\GetCurrencyRateRequest;
use App\Services\NBP\Requests\GetExchangeRatesRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NBPService
{
    public function __construct(
        protected NBPConnector $connector
    ) {
    }

    /**
     * Get exchange rate table for a specific date.
     */
    public function getExchangeRatesTable(string $table = 'A', ?Carbon $date = null): ?ExchangeRateTableDTO
    {
        $dateString = $date ? $date->format('Y-m-d') : null;

        $request  = new GetExchangeRatesRequest($table, $dateString);
        $response = $this->connector->send($request);

        if (!$response->successful()) {
            Log::error('NBP API request failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new \Exception('Failed to fetch exchange rates: ' . $response->body());
        }

        $data = $response->json();

        if (empty($data) || !isset($data[0]['rates'])) {
            return null;
        }

        return ExchangeRateTableDTO::fromArray($data[0]);
    }

    /**
     * Get all exchange rates as DTOs for a specific date.
     */
    public function getExchangeRates(string $table = 'A', ?Carbon $date = null): Collection
    {
        $tableDTO = $this->getExchangeRatesTable($table, $date);

        return $tableDTO ? $tableDTO->rates : collect();
    }

    /**
     * Get exchange rate for a specific currency.
     */
    public function getCurrencyRate(string $currencyCode, string $table = 'A', ?Carbon $date = null): ?ExchangeRateDTO
    {
        $dateString = $date ? $date->format('Y-m-d') : null;

        $request  = new GetCurrencyRateRequest($table, strtoupper($currencyCode), $dateString);
        $response = $this->connector->send($request);

        if (!$response->successful()) {
            Log::error('NBP API currency request failed', [
                'currency' => $currencyCode,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);

            return null;
        }

        $data = $response->json();

        if (empty($data['rates'])) {
            return null;
        }

        $currencyDTO = CurrencyRateDTO::fromArray($data);

        return $currencyDTO->toExchangeRateDTO();
    }

    /**
     * Get exchange rates for date range.
     */
    public function getExchangeRatesRange(Carbon $startDate, Carbon $endDate, string $table = 'A'): Collection
    {
        $endpoint = "/exchangerates/tables/{$table}/{$startDate->format('Y-m-d')}/{$endDate->format('Y-m-d')}";

        $request = new class($endpoint) extends \Saloon\Http\Request {
            protected \Saloon\Enums\Method $method = \Saloon\Enums\Method::GET;

            public function __construct(protected string $endpoint)
            {
            }

            public function resolveEndpoint(): string
            {
                return $this->endpoint;
            }
        };

        $response = $this->connector->send($request);

        if (!$response->successful()) {
            Log::error('NBP API range request failed', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date'   => $endDate->format('Y-m-d'),
                'status'     => $response->status(),
            ]);

            throw new \Exception('Failed to fetch exchange rates for date range');
        }

        $data  = $response->json();
        $rates = collect();

        foreach ($data as $dayData) {
            $tableDTO = ExchangeRateTableDTO::fromArray($dayData);
            $rates    = $rates->merge($tableDTO->rates);
        }

        return $rates;
    }

    /**
     * Get specific currency rate from table (convenience method).
     */
    public function getCurrencyFromTable(string $currencyCode, string $table = 'A', ?Carbon $date = null): ?ExchangeRateDTO
    {
        $tableDTO = $this->getExchangeRatesTable($table, $date);

        return $tableDTO?->getRateForCurrency($currencyCode);
    }
}
