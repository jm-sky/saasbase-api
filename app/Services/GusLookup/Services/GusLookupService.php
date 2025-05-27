<?php

namespace App\Services\GusLookup\Services;

use App\Domain\Common\Support\NipValidator\NipValidator;
use App\Domain\Common\Support\RegonValidator\RegonValidator;
use App\Services\GusLookup\DTOs\GusFullReportResultDTO;
use App\Services\GusLookup\Enums\CacheMode;
use App\Services\GusLookup\Exceptions\GusLookupException;
use App\Services\GusLookup\Integrations\GusApiConnector;
use App\Services\GusLookup\Integrations\Requests\GetFullReportRequest;
use App\Services\GusLookup\Integrations\Requests\SearchByNipRequest;
use App\Services\GusLookup\Integrations\Requests\SearchByRegonRequest;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GusLookupService
{
    protected GusApiConnector $connector;

    protected CacheMode $cacheMode;

    protected int $cacheHours;

    public function __construct(GusApiConnector $connector)
    {
        $this->connector    = $connector;
        $this->cacheMode    = CacheMode::from(config('gus_lookup.cache_mode', 'hours'));
        $this->cacheHours   = (int) config('gus_lookup.cache_hours', 12);
    }

    public function findByNip(string $nip, bool $force = false, ?CarbonInterface $now = null): ?GusFullReportResultDTO
    {
        $nip      = NipValidator::sanitizeAndValidate($nip);
        $cacheKey = "gus_lookup_nip.{$nip}";
        $cacheTtl = $this->getCacheExpiration($now ?? now());

        if ($force) {
            return $this->lookupAndCacheByNip($nip, $cacheKey, $cacheTtl);
        }

        return Cache::remember($cacheKey, $cacheTtl, fn () => $this->lookupByNip($nip));
    }

    public function findByRegon(string $regon, bool $force = false, ?CarbonInterface $now = null): ?GusFullReportResultDTO
    {
        $regon    = RegonValidator::sanitizeAndValidate($regon);
        $cacheKey = "gus_lookup_regon.{$regon}";
        $cacheTtl = $this->getCacheExpiration($now ?? now());

        if ($force) {
            return $this->lookupAndCacheByRegon($regon, $cacheKey, $cacheTtl);
        }

        return Cache::remember($cacheKey, $cacheTtl, fn () => $this->lookupByRegon($regon));
    }

    protected function lookupAndCacheByNip(string $nip, string $cacheKey, \DateTimeInterface|\DateInterval|int $cacheTtl): ?GusFullReportResultDTO
    {
        $result = $this->lookupByNip($nip);
        Cache::put($cacheKey, $result, $cacheTtl);

        return $result;
    }

    protected function lookupAndCacheByRegon(string $regon, string $cacheKey, \DateTimeInterface|\DateInterval|int $cacheTtl): ?GusFullReportResultDTO
    {
        $result = $this->lookupByRegon($regon);
        Cache::put($cacheKey, $result, $cacheTtl);

        return $result;
    }

    protected function lookupByNip(string $nip): ?GusFullReportResultDTO
    {
        try {
            // First, search for the entity by NIP
            $searchRequest  = new SearchByNipRequest($nip);
            $searchResponse = $this->connector->send($searchRequest);
            $dto            = $searchResponse->dtoOrFail();

            // Get full report for the entity
            $reportRequest  = new GetFullReportRequest($dto->regon);
            $reportResponse = $this->connector->send($reportRequest);

            return $reportResponse->dtoOrFail();
        } catch (\Throwable $e) {
            Log::error('GusLookupService error: ' . $e->getMessage(), [
                'nip'       => $nip,
                'exception' => get_class($e),
                'trace'     => $e->getTraceAsString(),
            ]);

            throw new GusLookupException('Failed to lookup company details. ' . $e->getMessage(), 0, $e);
        }
    }

    protected function lookupByRegon(string $regon): ?GusFullReportResultDTO
    {
        try {
            $searchRequest  = new SearchByRegonRequest($regon);
            $searchResponse = $this->connector->send($searchRequest);
            $dto            = $searchResponse->dtoOrFail();

            // Get full report for the entity
            $reportRequest  = new GetFullReportRequest($dto->regon);
            $reportResponse = $this->connector->send($reportRequest);

            return $reportResponse->dtoOrFail();
        } catch (\Throwable $e) {
            Log::error('GusLookupService error: ' . $e->getMessage(), [
                'regon'     => $regon,
                'exception' => get_class($e),
                'trace'     => $e->getTraceAsString(),
            ]);

            throw new GusLookupException('Failed to lookup company details. ' . $e->getMessage(), 0, $e);
        }
    }

    protected function getCacheExpiration(CarbonInterface $now): \DateTimeInterface|\DateInterval|int
    {
        if (CacheMode::WEEK === $this->cacheMode) {
            return $now->copy()->next('Sunday')->startOfDay();
        }

        return $now->copy()->addHours($this->cacheHours);
    }
}
