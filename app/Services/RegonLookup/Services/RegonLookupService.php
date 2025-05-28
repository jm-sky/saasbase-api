<?php

namespace App\Services\RegonLookup\Services;

use App\Services\RegonLookup\DTOs\RegonFullReportResultDTO;
use App\Services\RegonLookup\DTOs\RegonLookupResultDTO;
use App\Services\RegonLookup\Enums\CacheMode;
use App\Services\RegonLookup\Exceptions\RegonLookupException;
use App\Services\RegonLookup\Integrations\RegonApiConnector;
use App\Services\RegonLookup\Integrations\Requests\GetFullReportRequest;
use App\Services\RegonLookup\Integrations\Requests\SearchRequest;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RegonLookupService
{
    private readonly bool $shouldLog;

    private readonly CacheMode $cacheMode;

    private readonly int $cacheHours;

    public function __construct(
        private readonly RegonApiConnector $apiConnector
    ) {
        $this->shouldLog    = app()->isLocal() || config('regon_lookup.should_log', false);
        $this->cacheMode    = CacheMode::from(config('regon_lookup.cache_mode', 'hours'));
        $this->cacheHours   = (int) config('regon_lookup.cache_hours', 12);
    }

    public function findByNip(string $nip, bool $force = false, ?CarbonInterface $now = null): ?RegonFullReportResultDTO
    {
        $nip      = $this->sanitizeAndValidateNip($nip);
        $cacheKey = "regon_lookup_nip.{$nip}";

        return $this->lookupAndCacheByNip($nip, $cacheKey, $this->getCacheTtl($now));
    }

    public function findByRegon(string $regon, bool $force = false, ?CarbonInterface $now = null): ?RegonFullReportResultDTO
    {
        $regon    = $this->sanitizeAndValidateRegon($regon);
        $cacheKey = "regon_lookup_regon.{$regon}";

        return $this->lookupAndCacheByRegon($regon, $cacheKey, $this->getCacheTtl($now));
    }

    protected function getCacheTtl(?CarbonInterface $now = null): \DateTimeInterface|\DateInterval|int
    {
        return match ($this->cacheMode) {
            CacheMode::HOURS        => $this->cacheHours * 3600,
            CacheMode::END_OF_DAY   => $this->getEndOfDay($now),
            CacheMode::END_OF_MONTH => $this->getEndOfMonth($now),
        };
    }

    protected function lookupAndCacheByNip(string $nip, string $cacheKey, \DateTimeInterface|\DateInterval|int $cacheTtl): ?RegonFullReportResultDTO
    {
        return Cache::remember($cacheKey, $cacheTtl, function () use ($nip) {
            return $this->lookupByNip($nip);
        });
    }

    protected function lookupAndCacheByRegon(string $regon, string $cacheKey, \DateTimeInterface|\DateInterval|int $cacheTtl): ?RegonFullReportResultDTO
    {
        return Cache::remember($cacheKey, $cacheTtl, function () use ($regon) {
            return $this->lookupByRegon($regon);
        });
    }

    protected function lookupByNip(string $nip): ?RegonFullReportResultDTO
    {
        try {
            $searchResponse = $this->apiConnector->send(new SearchRequest(nip: $nip, regon: ''));
            $searchResult   = $searchResponse->dto();

            if (!$searchResult instanceof RegonLookupResultDTO) {
                return null;
            }

            $response = $this->apiConnector->send(new GetFullReportRequest(
                regon: $searchResult->regon
            ));

            return $response->dto();
        } catch (\Exception $e) {
            if ($this->shouldLog) {
                Log::error('RegonLookupService error: ' . $e->getMessage(), [
                    'nip'       => $nip,
                    'exception' => $e,
                ]);
            }

            return null;
        }
    }

    protected function lookupByRegon(string $regon): ?RegonFullReportResultDTO
    {
        try {
            $response = $this->apiConnector->send(new GetFullReportRequest(
                regon: $regon
            ));

            return $response->dto();
        } catch (\Exception $e) {
            if ($this->shouldLog) {
                Log::error('RegonLookupService error: ' . $e->getMessage(), [
                    'regon'     => $regon,
                    'exception' => $e,
                ]);
            }

            return null;
        }
    }

    protected function sanitizeAndValidateNip(string $nip): string
    {
        $nip = preg_replace('/[^0-9]/', '', $nip);

        if (10 !== strlen($nip)) {
            throw new RegonLookupException('Invalid NIP format. NIP must be 10 digits.');
        }

        return $nip;
    }

    protected function sanitizeAndValidateRegon(string $regon): string
    {
        $regon = preg_replace('/[^0-9]/', '', $regon);

        if (!in_array(strlen($regon), [9, 14])) {
            throw new RegonLookupException('Invalid REGON format. REGON must be 9 or 14 digits.');
        }

        return $regon;
    }

    protected function getEndOfDay(?CarbonInterface $now = null): \DateTimeInterface
    {
        $now = $now ?? now();

        return $now->copy()->endOfDay();
    }

    protected function getEndOfMonth(?CarbonInterface $now = null): \DateTimeInterface
    {
        $now = $now ?? now();

        return $now->copy()->endOfMonth();
    }
}
