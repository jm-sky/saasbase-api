<?php

namespace App\Domain\ViesLookup\Services;

use App\Domain\ViesLookup\DTOs\ViesLookupResultDTO;
use App\Domain\ViesLookup\Exceptions\ViesLookupException;
use App\Domain\ViesLookup\Integrations\ViesConnector;
use App\Domain\ViesLookup\Integrations\Requests\CheckVatRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ViesLookupService
{
    protected const DEFAULT_CACHE_HOURS = 12;

    protected ViesConnector $connector;

    public function __construct()
    {
        $this->connector = new ViesConnector();
    }

    public function findByVat(string $countryCode, string $vatNumber): ?ViesLookupResultDTO
    {
        $countryCode = strtoupper(trim($countryCode));
        $vatNumber = preg_replace('/[^0-9A-Za-z]/', '', $vatNumber);

        $cacheKey = "vies_lookup_{$countryCode}_{$vatNumber}";
        $cacheTtl = $this->getCacheExpiration();

        return Cache::remember($cacheKey, $cacheTtl, function () use ($countryCode, $vatNumber) {
            try {
                $request = new CheckVatRequest($countryCode, $vatNumber);
                $response = $this->connector->send($request);

                if ($response->successful()) {
                    $xml = simplexml_load_string($response->body());

                    if ($xml === false) {
                        throw new ViesLookupException('Invalid VIES XML response.');
                    }

                    $result = json_decode(json_encode($xml), true);

                    return ViesLookupResultDTO::fromApiResponse([
                        'countryCode' => $result['soap:Body']['checkVatResponse']['countryCode'] ?? '',
                        'vatNumber' => $result['soap:Body']['checkVatResponse']['vatNumber'] ?? '',
                        'valid' => $result['soap:Body']['checkVatResponse']['valid'] ?? false,
                        'name' => $result['soap:Body']['checkVatResponse']['name'] ?? null,
                        'address' => $result['soap:Body']['checkVatResponse']['address'] ?? null,
                    ]);
                }

                throw new ViesLookupException('Unsuccessful VIES API response.');
            } catch (\Throwable $e) {
                Log::error('ViesLookupService error: ' . $e->getMessage(), [
                    'countryCode' => $countryCode,
                    'vatNumber' => $vatNumber,
                ]);

                throw new ViesLookupException('Failed to lookup VAT number.', 0, $e);
            }
        });
    }

    protected function getCacheExpiration(): \DateTimeInterface|\DateInterval|int
    {
        $cacheMode = config('vies_lookup.cache_mode', 'hours');

        if ($cacheMode === 'week') {
            return now()->next('Sunday')->startOfDay();
        }

        $hours = (int) config('vies_lookup.cache_hours', self::DEFAULT_CACHE_HOURS);

        return now()->addHours($hours);
    }
}
