<?php

namespace App\Services\CompanyLookup\Services;

use App\Services\CompanyLookup\DTOs\CompanyLookupResultDTO;
use App\Services\CompanyLookup\Exceptions\CompanyLookupException;
use App\Services\CompanyLookup\Integrations\MfApiConnector;
use App\Services\CompanyLookup\Integrations\Requests\SearchByNipRequest;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CompanyLookupService
{
    protected MfApiConnector $connector;

    protected string $cacheMode;

    protected int $cacheHours;

    public function __construct(MfApiConnector $connector)
    {
        $this->connector    = $connector;
        $this->cacheMode    = config('company_lookup.cache_mode', 'hours');
        $this->cacheHours   = (int) config('company_lookup.cache_hours', 12);
    }

    public function findByNip(string $nip, bool $force = false, ?CarbonInterface $now = null): ?CompanyLookupResultDTO
    {
        $nip      = $this->sanitizeAndValidateNip($nip);
        $cacheKey = "company_lookup_nip.{$nip}";
        $cacheTtl = $this->getCacheExpiration($now ?? now());

        if ($force) {
            return $this->lookupAndCache($nip, $cacheKey, $cacheTtl);
        }

        return Cache::remember($cacheKey, $cacheTtl, fn () => $this->lookup($nip));
    }

    protected function lookupAndCache(string $nip, string $cacheKey, \DateTimeInterface|\DateInterval|int $cacheTtl): ?CompanyLookupResultDTO
    {
        $result = $this->lookup($nip);
        Cache::put($cacheKey, $result, $cacheTtl);

        return $result;
    }

    protected function lookup(string $nip): ?CompanyLookupResultDTO
    {
        try {
            $request  = new SearchByNipRequest($nip);
            $response = $this->connector->send($request);

            if ($response->successful()) {
                $subject = $response->json('result.subject');

                if (!empty($subject)) {
                    return CompanyLookupResultDTO::fromApiResponse($subject);
                }

                return null;
            }

            Log::warning('CompanyLookupService: Unsuccessful API response', [
                'nip'      => $nip,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);

            throw new CompanyLookupException('Unsuccessful API response.');
        } catch (\Throwable $e) {
            Log::error('CompanyLookupService error: ' . $e->getMessage(), [
                'nip'       => $nip,
                'exception' => get_class($e),
                'trace'     => $e->getTraceAsString(),
            ]);

            throw new CompanyLookupException('Failed to lookup company details.', 0, $e);
        }
    }

    protected function sanitizeAndValidateNip(string $nip): string
    {
        $nip = preg_replace('/[^0-9]/', '', $nip);

        if (10 !== strlen($nip)) {
            throw new CompanyLookupException('Invalid NIP format. NIP must be 10 digits.');
        }

        return $nip;
    }

    protected function getCacheExpiration(CarbonInterface $now): \DateTimeInterface|\DateInterval|int
    {
        if ('week' === $this->cacheMode) {
            return $now->copy()->next('Sunday')->startOfDay();
        }

        return $now->copy()->addHours($this->cacheHours);
    }
}
