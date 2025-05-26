<?php

namespace App\Services\GusLookup\Services;

use App\Services\GusLookup\DTOs\GusLookupResultDTO;
use App\Services\GusLookup\Exceptions\GusLookupException;
use App\Services\GusLookup\Integrations\GusApiConnector;
use App\Services\GusLookup\Integrations\Requests\GetFullReportRequest;
use App\Services\GusLookup\Integrations\Requests\LoginRequest;
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

    public function findByNip(string $nip, bool $force = false, ?CarbonInterface $now = null): ?GusLookupResultDTO
    {
        $nip      = $this->sanitizeAndValidateNip($nip);
        $cacheKey = "gus_lookup_nip.{$nip}";
        $cacheTtl = $this->getCacheExpiration($now ?? now());

        if ($force) {
            return $this->lookupAndCacheByNip($nip, $cacheKey, $cacheTtl);
        }

        return Cache::remember($cacheKey, $cacheTtl, fn () => $this->lookupByNip($nip));
    }

    public function findByRegon(string $regon, bool $force = false, ?CarbonInterface $now = null): ?GusLookupResultDTO
    {
        $regon    = $this->sanitizeAndValidateRegon($regon);
        $cacheKey = "gus_lookup_regon.{$regon}";
        $cacheTtl = $this->getCacheExpiration($now ?? now());

        if ($force) {
            return $this->lookupAndCache($regon, $cacheKey, $cacheTtl);
        }

        return Cache::remember($cacheKey, $cacheTtl, fn () => $this->lookup($regon));
    }

    protected function lookupAndCacheByNip(string $nip, string $cacheKey, \DateTimeInterface|\DateInterval|int $cacheTtl): ?GusLookupResultDTO
    {
        $result = $this->lookupByNip($nip);
        Cache::put($cacheKey, $result, $cacheTtl);

        return $result;
    }

    protected function lookupAndCache(string $regon, string $cacheKey, \DateTimeInterface|\DateInterval|int $cacheTtl): ?GusLookupResultDTO
    {
        $result = $this->lookup($regon);
        Cache::put($cacheKey, $result, $cacheTtl);

        return $result;
    }

    protected function lookupByNip(string $nip): ?GusLookupResultDTO
    {
        try {
            // Ensure we have a valid session
            $this->ensureValidSession();

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

            // Parse the search response to get REGON
            $xml = simplexml_load_string($searchResponse->body());

            if (false === $xml) {
                throw new GusLookupException('Invalid XML response from search.');
            }

            $result = json_decode(json_encode($xml), true);
            $regon  = $result['soap:Body']['DaneSzukajPodmiotyResponse']['DaneSzukajPodmiotyResult']['Regon'] ?? null;

            if (!$regon) {
                return null;
            }

            // Get full report for the entity
            $reportRequest  = new GetFullReportRequest($regon, 'BIR11OsPrawna');
            $reportResponse = $this->connector->send($reportRequest);

            if (!$reportResponse->successful()) {
                Log::warning('GusLookupService: Unsuccessful report response', [
                    'regon'    => $regon,
                    'status'   => $reportResponse->status(),
                    'body'     => $reportResponse->body(),
                ]);

                throw new GusLookupException('Unsuccessful report response.');
            }

            // Parse the report response
            $xml = simplexml_load_string($reportResponse->body());

            if (false === $xml) {
                throw new GusLookupException('Invalid XML response from report.');
            }

            $result = json_decode(json_encode($xml), true);
            $data   = $result['soap:Body']['DanePobierzPelnyRaportResponse']['DanePobierzPelnyRaportResult'] ?? null;

            if (!$data) {
                return null;
            }

            return GusLookupResultDTO::fromApiResponse($data);
        } catch (\Throwable $e) {
            Log::error('GusLookupService error: ' . $e->getMessage(), [
                'nip'       => $nip,
                'exception' => get_class($e),
                'trace'     => $e->getTraceAsString(),
            ]);

            throw new GusLookupException('Failed to lookup company details. ' . $e->getMessage(), 0, $e);
        }
    }

    protected function ensureValidSession(): void
    {
        if (!$this->connector->getSessionId()) {
            $this->login();
        }
    }

    protected function login(): void
    {
        $userKey = config('gus_lookup.user_key');

        if (!$userKey) {
            throw new GusLookupException('GUS API user key not configured.');
        }

        $request  = new LoginRequest($userKey);
        $response = $this->connector->send($request);

        if (!$response->successful()) {
            throw new GusLookupException('Failed to login to GUS API.');
        }

        $xml = simplexml_load_string($response->body());

        if (false === $xml) {
            throw new GusLookupException('Invalid XML response from login.');
        }

        $result    = json_decode(json_encode($xml), true);
        $sessionId = $result['soap:Body']['ZalogujResponse']['ZalogujResult'] ?? null;

        if (!$sessionId) {
            throw new GusLookupException('No session ID received from login.');
        }

        $this->connector->setSessionId($sessionId);
    }

    protected function lookup(string $regon): ?GusLookupResultDTO
    {
        try {
            $request  = new SearchByRegonRequest($regon);
            $response = $this->connector->send($request);

            if ($response->successful()) {
                $subject = $response->json('result.subject');

                if (!empty($subject)) {
                    return GusLookupResultDTO::fromApiResponse($subject);
                }

                return null;
            }

            Log::warning('GusLookupService: Unsuccessful API response', [
                'regon'    => $regon,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);

            throw new GusLookupException('Unsuccessful API response.');
        } catch (\Throwable $e) {
            Log::error('GusLookupService error: ' . $e->getMessage(), [
                'regon'     => $regon,
                'exception' => get_class($e),
                'trace'     => $e->getTraceAsString(),
            ]);

            throw new GusLookupException('Failed to lookup company details.', 0, $e);
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
