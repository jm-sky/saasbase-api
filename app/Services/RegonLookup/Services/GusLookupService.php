<?php

namespace App\Services\RegonLookup\Services;

use App\Domain\Common\Support\NipValidator\NipValidator;
use App\Domain\Common\Support\RegonValidator\RegonValidator;
use App\Services\RegonLookup\DTOs\GusFullReportResultDTO;
use App\Services\RegonLookup\Enums\CacheMode;
use App\Services\RegonLookup\Exceptions\RegonLookupException;
use App\Services\RegonLookup\Integrations\RegonApiConnector;
use App\Services\RegonLookup\Integrations\Requests\GetFullReportRequest;
use App\Services\RegonLookup\Integrations\Requests\SearchByNipRequest;
use App\Services\RegonLookup\Integrations\Requests\SearchByRegonRequest;
use App\Services\RegonLookup\RegonReportResolver;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RegonLookupService
{
    protected bool $shouldLog = false;

    protected RegonApiConnector $connector;

    protected CacheMode $cacheMode;

    protected int $cacheHours;

    public function __construct(RegonApiConnector $connector)
    {
        $this->shouldLog    = app()->isLocal() || config('gus_lookup.should_log', false);
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

    protected function log(string $message, array $context = []): void
    {
        if ($this->shouldLog) {
            Log::debug($message, $context);
        }
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
            $this->log('[lookupByNip] Looking up company by NIP', ['nip' => $nip]);

            // First, search for the entity by NIP
            $searchRequest  = new SearchByNipRequest($nip);
            $searchResponse = $this->connector->send($searchRequest);
            $this->log('[lookupByNip] Search response', ['searchResponse' => $searchResponse]);
            $dto            = $searchResponse->dtoOrFail();

            // Get full report for the entity
            $reportName     = RegonReportResolver::resolve($dto->toArray());
            $reportRequest  = new GetFullReportRequest($dto->regon, $reportName);
            $reportResponse = $this->connector->send($reportRequest);
            $this->log('[lookupByNip] Report response', ['reportResponse' => $reportResponse]);

            return $reportResponse->dtoOrFail();
        } catch (\Throwable $e) {
            Log::error('GusLookupService error: ' . $e->getMessage(), [
                'nip'       => $nip,
                'exception' => get_class($e),
                'trace'     => $e->getTraceAsString(),
            ]);

            throw new RegonLookupException('Failed to lookup company details. ' . $e->getMessage(), 0, $e);
        }
    }

    protected function lookupByRegon(string $regon): ?GusFullReportResultDTO
    {
        try {
            $searchRequest  = new SearchByRegonRequest($regon);
            $searchResponse = $this->connector->send($searchRequest);
            $dto            = $searchResponse->dtoOrFail();

            // Get full report for the entity
            $reportName     = RegonReportResolver::resolve($dto->toArray());
            $reportRequest  = new GetFullReportRequest($dto->regon, $reportName);
            $reportResponse = $this->connector->send($reportRequest);

            return $reportResponse->dtoOrFail();
        } catch (\Throwable $e) {
            Log::error('GusLookupService error: ' . $e->getMessage(), [
                'regon'     => $regon,
                'exception' => get_class($e),
                'trace'     => $e->getTraceAsString(),
            ]);

            throw new RegonLookupException('Failed to lookup company details. ' . $e->getMessage(), 0, $e);
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
