<?php

namespace App\Services\IbanInfo;

use App\Domain\IbanInfo\Models\BankCode;
use Illuminate\Support\Facades\Cache;

class IbanCacheService
{
    private const CACHE_TTL_DAYS = 30;

    private const CACHE_KEY_PREFIX = 'bank_code';

    /**
     * Get bank code information from the cache.
     */
    public function get(string $countryCode, string $bankCode): ?BankCode
    {
        $cacheKey = $this->generateCacheKey($countryCode, $bankCode);

        return Cache::get($cacheKey);
    }

    /**
     * Store bank code information in the cache.
     */
    public function put(BankCode $bankCode): void
    {
        $cacheKey = $this->generateCacheKey($bankCode->country_code, $bankCode->bank_code);
        Cache::put($cacheKey, $bankCode, now()->addDays(self::CACHE_TTL_DAYS));
    }

    /**
     * Remove bank code information from the cache.
     */
    public function forget(string $countryCode, string $bankCode): void
    {
        $cacheKey = $this->generateCacheKey($countryCode, $bankCode);
        Cache::forget($cacheKey);
    }

    /**
     * Generate a unique cache key for a bank code.
     */
    private function generateCacheKey(string $countryCode, string $bankCode): string
    {
        return self::CACHE_KEY_PREFIX . ":{$countryCode}_{$bankCode}";
    }
}
