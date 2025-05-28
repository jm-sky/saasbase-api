<?php

namespace App\Services\MfLookup\Services;

use App\Domain\Common\Support\NipValidator\NipValidator;
use App\Services\MfLookup\DTOs\MfLookupResultDTO;
use App\Services\MfLookup\Exceptions\MfLookupException;
use App\Services\MfLookup\Integrations\MfApiConnector;
use App\Services\MfLookup\Integrations\Requests\SearchByNipRequest;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MfLookupService
{
    protected MfApiConnector $connector;

    protected string $cacheMode;

    protected int $cacheHours;

    public function __construct(MfApiConnector $connector)
    {
        $this->connector    = $connector;
        $this->cacheMode    = config('mf_lookup.cache_mode', 'hours');
        $this->cacheHours   = (int) config('mf_lookup.cache_hours', 12);
    }

    public function findByNip(string $nip, bool $force = false, ?CarbonInterface $now = null): ?MfLookupResultDTO
    {
        $nip      = $this->sanitizeAndValidateNip($nip);
        $cacheKey = "mf_lookup_nip.{$nip}";
        $cacheTtl = $this->getCacheExpiration($now ?? now());

        if ($force) {
            return $this->lookupAndCache($nip, $cacheKey, $cacheTtl);
        }

        return Cache::remember($cacheKey, $cacheTtl, fn () => $this->lookup($nip));
    }

    protected function lookupAndCache(string $nip, string $cacheKey, \DateTimeInterface|\DateInterval|int $cacheTtl): ?MfLookupResultDTO
    {
        $result = $this->lookup($nip);
        Cache::put($cacheKey, $result, $cacheTtl);

        return $result;
    }

    protected function lookup(string $nip): ?MfLookupResultDTO
    {
        try {
            $request  = new SearchByNipRequest($nip);
            $response = $this->connector->send($request);

            if ($response->successful()) {
                $subject = $response->json('result.subject');

                if (!empty($subject)) {
                    return MfLookupResultDTO::fromApiResponse($subject);
                }

                return null;
            }

            Log::warning('MfLookupService: Unsuccessful API response', [
                'nip'      => $nip,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);

            throw new MfLookupException('Unsuccessful API response.');
        } catch (\Throwable $e) {
            Log::error('MfLookupService error: ' . $e->getMessage(), [
                'nip'       => $nip,
                'exception' => get_class($e),
                'trace'     => $e->getTraceAsString(),
            ]);

            throw new MfLookupException('Failed to lookup company details.', 0, $e);
        }
    }

    protected function sanitizeAndValidateNip(string $nip): string
    {
        try {
            return NipValidator::sanitizeAndValidate($nip);
        } catch (\App\Domain\Common\Support\NipValidator\Exceptions\InvalidNipException $e) {
            throw new MfLookupException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function getCacheExpiration(CarbonInterface $now): \DateTimeInterface|\DateInterval|int
    {
        if ('week' === $this->cacheMode) {
            return $now->copy()->next('Sunday')->startOfDay();
        }

        return $now->copy()->addHours($this->cacheHours);
    }
}
