<?php

namespace App\Services\GusLookup\Services;

use App\Services\GusLookup\DTOs\GusFullReportResultDTO;
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

    protected string $cacheMode;

    protected int $cacheHours;

    public function __construct(GusApiConnector $connector)
    {
        $this->connector    = $connector;
        $this->cacheMode    = config('gus_lookup.cache_mode', 'hours');
        $this->cacheHours   = (int) config('gus_lookup.cache_hours', 12);
    }

    public function findByNip(string $nip, bool $force = false, ?CarbonInterface $now = null): ?GusFullReportResultDTO
    {
        $nip      = $this->sanitizeAndValidateNip($nip);
        $cacheKey = "gus_lookup_nip.{$nip}";
        $cacheTtl = $this->getCacheExpiration($now ?? now());

        if ($force) {
            return $this->lookupAndCacheByNip($nip, $cacheKey, $cacheTtl);
        }

        return Cache::remember($cacheKey, $cacheTtl, fn () => $this->lookupByNip($nip));
    }

    public function findByRegon(string $regon, bool $force = false, ?CarbonInterface $now = null): ?GusFullReportResultDTO
    {
        $regon    = $this->sanitizeAndValidateRegon($regon);
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

            if (!$searchResponse->successful()) {
                Log::warning('GusLookupService: Unsuccessful search response', [
                    'nip'      => $nip,
                    'status'   => $searchResponse->status(),
                    'body'     => $searchResponse->body(),
                ]);

                throw new GusLookupException('Unsuccessful search response.');
            }

            $dto = $searchRequest->createDtoFromResponse($searchResponse);

            // Get full report for the entity
            $reportRequest  = new GetFullReportRequest($dto->regon, 'BIR11OsPrawna');
            $reportResponse = $this->connector->send($reportRequest);

            if (!$reportResponse->successful()) {
                Log::warning('GusLookupService: Unsuccessful report response', [
                    'regon'    => $dto->regon,
                    'status'   => $reportResponse->status(),
                    'body'     => $reportResponse->body(),
                ]);

                throw new GusLookupException('Unsuccessful report response.');
            }

            /** @var GusFullReportResultDTO $dto */
            return $reportRequest->createDtoFromResponse($reportResponse);
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

            if (!$searchResponse->successful()) {
                Log::warning('GusLookupService: Unsuccessful search response', [
                    'regon'    => $regon,
                    'status'   => $searchResponse->status(),
                    'body'     => $searchResponse->body(),
                ]);

                throw new GusLookupException('Unsuccessful search response.');
            }

            $dto = $searchRequest->createDtoFromResponse($searchResponse);

            // Get full report for the entity
            $reportRequest  = new GetFullReportRequest($dto->regon, 'BIR11OsPrawna');
            $reportResponse = $this->connector->send($reportRequest);

            if (!$reportResponse->successful()) {
                Log::warning('GusLookupService: Unsuccessful report response', [
                    'regon'    => $dto->regon,
                    'status'   => $reportResponse->status(),
                    'body'     => $reportResponse->body(),
                ]);

                throw new GusLookupException('Unsuccessful report response.');
            }

            /* @var GusFullReportResultDTO $dto */
            return $reportRequest->createDtoFromResponse($reportResponse);
        } catch (\Throwable $e) {
            Log::error('GusLookupService error: ' . $e->getMessage(), [
                'regon'     => $regon,
                'exception' => get_class($e),
                'trace'     => $e->getTraceAsString(),
            ]);

            throw new GusLookupException('Failed to lookup company details. ' . $e->getMessage(), 0, $e);
        }
    }

    protected function sanitizeAndValidateNip(string $nip): string
    {
        $nip = preg_replace('/[^0-9]/', '', $nip);

        if (10 !== strlen($nip)) {
            throw new GusLookupException('Invalid NIP format. NIP must be 10 digits.');
        }

        return $nip;
    }

    protected function sanitizeAndValidateRegon(string $regon): string
    {
        $regon = preg_replace('/[^0-9]/', '', $regon);

        if (!in_array(strlen($regon), [9, 14])) {
            throw new GusLookupException('Invalid REGON format. REGON must be 9 or 14 digits.');
        }

        return $regon;
    }

    protected function getCacheExpiration(CarbonInterface $now): \DateTimeInterface|\DateInterval|int
    {
        if ('week' === $this->cacheMode) {
            return $now->copy()->next('Sunday')->startOfDay();
        }

        return $now->copy()->addHours($this->cacheHours);
    }
}
