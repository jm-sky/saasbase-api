<?php

namespace App\Domain\CompanyLookup\Services;

use App\Domain\CompanyLookup\DTOs\CompanyLookupResultDTO;
use App\Domain\CompanyLookup\Exceptions\CompanyLookupException;
use App\Domain\CompanyLookup\Integrations\MfApiConnector;
use App\Domain\CompanyLookup\Integrations\Requests\SearchByNipRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CompanyLookupService
{
    protected const DEFAULT_CACHE_HOURS = 12;

    protected MfApiConnector $connector;

    public function __construct()
    {
        $this->connector = new MfApiConnector();
    }

    public function findByNip(string $nip): ?CompanyLookupResultDTO
    {
        $nip = preg_replace('/[^0-9]/', '', $nip);

        $cacheKey = "company_lookup_nip.{$nip}";
        $cacheTtl = $this->getCacheExpiration();

        return Cache::remember($cacheKey, $cacheTtl, function () use ($nip) {
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

                throw new CompanyLookupException('Unsuccessful API response.');
            } catch (\Throwable $e) {
                Log::error('CompanyLookupService error: ' . $e->getMessage(), [
                    'nip' => $nip,
                ]);

                throw new CompanyLookupException('Failed to lookup company details.', 0, $e);
            }
        });
    }

    protected function getCacheExpiration(): \DateTimeInterface|\DateInterval|int
    {
        $cacheMode = config('company_lookup.cache_mode', 'hours');

        if ('week' === $cacheMode) {
            return now()->next('Sunday')->startOfDay();
        }

        $hours = (int) config('company_lookup.cache_hours', self::DEFAULT_CACHE_HOURS);

        return now()->addHours($hours);
    }
}
