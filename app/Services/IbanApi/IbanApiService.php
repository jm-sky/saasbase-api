<?php

namespace App\Services\IbanApi;

use App\Services\IbanApi\DTOs\IbanApiResponse;
use App\Services\IbanApi\Integrations\IbanApiConnector;
use App\Services\IbanApi\Integrations\Requests\ValidateIbanRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IbanApiService
{
    protected const DEFAULT_CACHE_HOURS = 12;

    public static bool $throw = false;

    protected IbanApiConnector $connector;

    public function __construct(?IbanApiConnector $connector = null)
    {
        $this->connector = $connector ?? new IbanApiConnector();
    }

    public function getIbanInfo(string $iban, ?bool $throw = null, bool $force = false): ?IbanApiResponse
    {
        $iban     = trim($iban);
        $cacheKey = "iban_lookup:{$iban}";
        $cacheTtl = $this->getCacheExpiration();
        $throw    = $throw ?? self::$throw;

        if ($force) {
            return $this->lookupAndCache($iban, $cacheKey, $cacheTtl, $throw);
        }

        return Cache::remember($cacheKey, $cacheTtl, function () use ($iban, $throw) {
            return $this->lookup($iban, $throw);
        });
    }

    protected function lookupAndCache(string $iban, string $cacheKey, \DateTimeInterface|\DateInterval|int $cacheTtl, ?bool $throw = null): ?IbanApiResponse
    {
        $throw    = $throw ?? self::$throw;
        $result   = $this->lookup($iban, $throw);
        Cache::put($cacheKey, $result, $cacheTtl);

        return $result;
    }

    protected function lookup(string $iban, ?bool $throw = null): ?IbanApiResponse
    {
        try {
            $throw    = $throw ?? self::$throw;
            $response = $this->connector->send(new ValidateIbanRequest($iban));

            if ($throw) {
                return $response->dtoOrFail();
            }

            if ($response->failed()) {
                return null;
            }

            return $response->dto();
        } catch (\Throwable $e) {
            Log::error('IbanApiService error: ' . $e->getMessage(), [
                'iban' => $iban,
            ]);

            if ($throw) {
                throw $e;
            }

            return null;
        }
    }

    public function getSwiftForIban(string $iban, ?bool $throw = null, bool $force = false): ?string
    {
        $throw    = $throw ?? self::$throw;
        $info     = $this->getIbanInfo($iban, $throw, $force);

        return $info?->data?->bank?->bic;
    }

    protected function getCacheExpiration(): \DateTimeInterface|\DateInterval|int
    {
        $cacheMode = config('services.ibanapi.cache.mode', 'hours');

        if ('week' === $cacheMode) {
            return now()->next('Sunday')->startOfDay();
        }

        $hours = (int) config('services.ibanapi.cache.hours', self::DEFAULT_CACHE_HOURS);

        return now()->addHours($hours);
    }
}
