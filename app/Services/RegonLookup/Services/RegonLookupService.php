<?php

namespace App\Services\RegonLookup\Services;

use App\Services\RegonLookup\DTOs\RegonLookupResultDTO;
use App\Services\RegonLookup\DTOs\RegonReportForLegalPerson;
use App\Services\RegonLookup\DTOs\RegonReportForNaturalPerson;
use App\Services\RegonLookup\DTOs\RegonReportUnified;
use App\Services\RegonLookup\Enums\CacheMode;
use App\Services\RegonLookup\Exceptions\RegonLookupException;
use App\Services\RegonLookup\Integrations\RegonApiConnector;
use App\Services\RegonLookup\Integrations\Requests\GetFullReportRequest;
use App\Services\RegonLookup\Integrations\Requests\SearchRequest;
use App\Services\RegonLookup\Support\RegonReportResolver;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RegonLookupService
{
    public const DEFAULT_CACHE_HOURS = 12;

    private readonly bool $shouldLog;

    private readonly CacheMode $cacheMode;

    private readonly int $cacheHours;

    public function __construct(
        private readonly RegonApiConnector $apiConnector
    ) {
        $this->shouldLog    = app()->isLocal() || config('services.regon.should_log', false);
        $this->cacheMode    = CacheMode::from(config('services.regon.cache_mode', 'hours'));
        $this->cacheHours   = (int) config('services.regon.cache_hours', self::DEFAULT_CACHE_HOURS);
    }

    /**
     * @throws RegonLookupException
     */
    public function findByNip(string $nip, bool $force = false, ?CarbonInterface $now = null, bool $throw = false): ?RegonReportUnified
    {
        $nip      = $this->sanitizeAndValidateNip($nip);
        $cacheKey = "regon_lookup:nip:{$nip}";
        $cacheTtl = $this->getCacheTtl($now);

        if ($force) {
            return $this->lookupAndCacheByNip($nip, $cacheKey, $cacheTtl, $throw);
        }

        return Cache::remember($cacheKey, $cacheTtl, fn () => $this->lookupByNip($nip, $throw));
    }

    public function findByRegon(string $regon, bool $force = false, ?CarbonInterface $now = null, bool $throw = false): ?RegonReportUnified
    {
        $regon    = $this->sanitizeAndValidateRegon($regon);
        $cacheKey = "regon_lookup:regon:{$regon}";
        $cacheTtl = $this->getCacheTtl($now);

        if ($force) {
            return $this->lookupAndCacheByRegon($regon, $cacheKey, $cacheTtl, $throw);
        }

        return Cache::remember($cacheKey, $cacheTtl, fn () => $this->lookupByRegon($regon, $throw));
    }

    protected function getCacheTtl(?CarbonInterface $now = null): \DateTimeInterface|\DateInterval|int
    {
        return match ($this->cacheMode) {
            CacheMode::HOURS        => $this->cacheHours * 3600,
            CacheMode::END_OF_DAY   => $this->getEndOfDay($now),
            CacheMode::END_OF_MONTH => $this->getEndOfMonth($now),
        };
    }

    protected function lookupAndCacheByNip(string $nip, string $cacheKey, \DateTimeInterface|\DateInterval|int $cacheTtl, bool $throw = false): ?RegonReportUnified
    {
        $result = $this->lookupByNip($nip, $throw);
        Cache::put($cacheKey, $result, $cacheTtl);

        return $result;
    }

    protected function lookupAndCacheByRegon(string $regon, string $cacheKey, \DateTimeInterface|\DateInterval|int $cacheTtl, bool $throw = false): ?RegonReportUnified
    {
        $result = $this->lookupByRegon($regon, $throw);
        Cache::put($cacheKey, $result, $cacheTtl);

        return $result;
    }

    protected function lookupByNip(string $nip, bool $throw = false): ?RegonReportUnified
    {
        try {
            $searchResponse = $this->apiConnector->send(new SearchRequest(nip: $nip, regon: ''));
            $searchResult   = $searchResponse->dto();

            if (!$searchResult instanceof RegonLookupResultDTO) {
                if ($throw) {
                    throw new RegonLookupException('Invalid NIP format. NIP must be 10 digits.');
                }

                return null;
            }

            $reportName    = RegonReportResolver::resolveReportName($searchResult->type);
            $reportRequest = new GetFullReportRequest($searchResult->regon, $reportName, $searchResult->nip);
            $response      = $this->apiConnector->send($reportRequest);

            /** @var RegonReportForLegalPerson|RegonReportForNaturalPerson $dto */
            $dto = $response->dto();

            return $dto->toUnifiedReportDto();
        } catch (\Exception $e) {
            if ($throw) {
                throw $e;
            }

            if ($this->shouldLog) {
                Log::error('RegonLookupService error: ' . $e->getMessage(), [
                    'nip'       => $nip,
                    'exception' => $e,
                ]);
            }

            return null;
        }
    }

    protected function lookupByRegon(string $regon, bool $throw = false): ?RegonReportUnified
    {
        try {
            $response = $this->apiConnector->send(new GetFullReportRequest(
                regon: $regon
            ));

            /** @var RegonReportForLegalPerson|RegonReportForNaturalPerson $dto */
            $dto = $response->dto();

            return $dto->toUnifiedReportDto();
        } catch (\Exception $e) {
            if ($throw) {
                throw $e;
            }

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
