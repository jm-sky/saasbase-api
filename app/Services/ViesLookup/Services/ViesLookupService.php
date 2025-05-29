<?php

namespace App\Services\ViesLookup\Services;

use App\Services\ViesLookup\DTOs\ViesLookupResultDTO;
use App\Services\ViesLookup\Exceptions\ViesLookupException;
use App\Services\ViesLookup\Integrations\Requests\CheckVatRequest;
use App\Services\ViesLookup\Integrations\ViesConnector;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ViesLookupService
{
    protected const DEFAULT_CACHE_HOURS = 12;

    protected ViesConnector $connector;

    public function __construct(?ViesConnector $connector = null)
    {
        $this->connector = $connector ?? new ViesConnector();
    }

    /**
     * @throws ViesLookupException
     */
    public function findByVat(string $countryCode, string $vatNumber, bool $force = false): ?ViesLookupResultDTO
    {
        $countryCode = strtoupper(trim($countryCode));
        $vatNumber   = preg_replace('/[^0-9A-Za-z]/', '', $vatNumber);

        $cacheKey = "vies_lookup:{$countryCode}:{$vatNumber}";
        $cacheTtl = $this->getCacheExpiration();

        if ($force) {
            return $this->lookupAndCache($countryCode, $vatNumber, $cacheKey, $cacheTtl);
        }

        return Cache::remember($cacheKey, $cacheTtl, function () use ($countryCode, $vatNumber) {
            return $this->lookup($countryCode, $vatNumber);
        });
    }

    protected function lookupAndCache(string $countryCode, string $vatNumber, string $cacheKey, \DateTimeInterface|\DateInterval|int $cacheTtl): ?ViesLookupResultDTO
    {
        $result = $this->lookup($countryCode, $vatNumber);
        Cache::put($cacheKey, $result, $cacheTtl);

        return $result;
    }

    /**
     * @throws ViesLookupException
     */
    public function lookup(string $countryCode, string $vatNumber): ?ViesLookupResultDTO
    {
        try {
            $request  = new CheckVatRequest($countryCode, $vatNumber);
            $response = $this->connector->send($request);

            if ($response->successful()) {
                return $response->dtoOrFail();
            }

            throw new ViesLookupException('Unsuccessful VIES API response: ' . $response->status());
        } catch (\Throwable $e) {
            Log::error('ViesLookupService error: ' . $e->getMessage(), [
                'countryCode' => $countryCode,
                'vatNumber'   => $vatNumber,
            ]);

            if ($e instanceof ViesLookupException) {
                throw $e;
            }

            throw new ViesLookupException($e->getMessage(), 0, $e);
        }
    }

    protected function getCacheExpiration(): \DateTimeInterface|\DateInterval|int
    {
        $cacheMode = config('vies_lookup.cache_mode', 'hours');

        if ('week' === $cacheMode) {
            return now()->next('Sunday')->startOfDay();
        }

        $hours = (int) config('vies_lookup.cache_hours', self::DEFAULT_CACHE_HOURS);

        return now()->addHours($hours);
    }
}
