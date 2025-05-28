<?php

namespace App\Services\ViesLookup\Services;

use App\Services\ViesLookup\DTOs\ViesLookupResultDTO;
use App\Services\ViesLookup\Exceptions\ViesLookupException;
use App\Services\ViesLookup\Integrations\Requests\CheckVatRequest;
use App\Services\ViesLookup\Integrations\ViesConnector;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ViesLookupService
{
    protected const DEFAULT_CACHE_HOURS = 12;

    protected ViesConnector $connector;

    public function __construct(?ViesConnector $connector = null)
    {
        $this->connector = $connector ?? new ViesConnector();
    }

    public function findByVat(string $countryCode, string $vatNumber): ?ViesLookupResultDTO
    {
        $countryCode = strtoupper(trim($countryCode));
        $vatNumber   = preg_replace('/[^0-9A-Za-z]/', '', $vatNumber);

        $cacheKey = "vies_lookup_{$countryCode}_{$vatNumber}";
        $cacheTtl = $this->getCacheExpiration();

        return Cache::remember($cacheKey, $cacheTtl, function () use ($countryCode, $vatNumber) {
            try {
                $request  = new CheckVatRequest($countryCode, $vatNumber);
                $response = $this->connector->send($request);

                if ($response->successful()) {
                    $xml = @simplexml_load_string($response->body());

                    if (false === $xml) {
                        throw new ViesLookupException('Invalid VIES XML response.');
                    }

                    $namespaces = $xml->getNamespaces(true);
                    $soapNS     = $namespaces['soap'] ?? null;
                    $body       = $soapNS ? $xml->children($soapNS)->Body : $xml->Body;

                    if (!$body) {
                        throw new ViesLookupException('Invalid VIES XML response: No SOAP Body.');
                    }

                    // Check for SOAP Fault
                    $fault = $soapNS ? $body->children($soapNS)->Fault : $body->Fault;

                    if ($fault) {
                        // Fault may be an array or object
                        if (is_array($fault)) {
                            $fault = $fault[0] ?? null;
                        }
                        $faultString = null;

                        if ($fault && isset($fault->faultstring)) {
                            $faultString = (string) $fault->faultstring;
                        }

                        if ($faultString) {
                            throw new ViesLookupException($faultString);
                        }

                        throw new ViesLookupException('Unknown SOAP fault');
                    }

                    // Extract checkVatResponse (with its own namespace)
                    $viesNS = null;

                    foreach ($namespaces as $prefix => $uri) {
                        if (str_contains($uri, 'vies:services:checkVat:types')) {
                            $viesNS = $uri;
                            break;
                        }
                    }
                    $checkVatResponse = $viesNS ? $body->children($viesNS)->checkVatResponse : $body->checkVatResponse;

                    if (!$checkVatResponse) {
                        throw new ViesLookupException('Invalid VIES XML response: No checkVatResponse.');
                    }

                    // Extract fields
                    $countryCodeVal = (string) ($checkVatResponse->countryCode ?? '');
                    $vatNumberVal   = (string) ($checkVatResponse->vatNumber ?? '');
                    $validVal       = ((string) ($checkVatResponse->valid ?? '')) === 'true';
                    $nameVal        = (string) ($checkVatResponse->name ?? null);
                    $addressVal     = (string) ($checkVatResponse->address ?? null);

                    return ViesLookupResultDTO::fromApiResponse([
                        'countryCode' => $countryCodeVal,
                        'vatNumber'   => $vatNumberVal,
                        'valid'       => $validVal,
                        'name'        => $nameVal,
                        'address'     => $addressVal,
                    ]);
                }

                throw new ViesLookupException('Unsuccessful VIES API response: ' . $response->status());
            } catch (\Throwable $e) {
                Log::error('ViesLookupService error: ' . $e->getMessage(), [
                    'countryCode' => $countryCode,
                    'vatNumber'   => $vatNumber,
                ]);

                if ($e instanceof ViesLookupException) {
                    throw $e;
                }

                throw new ViesLookupException($e->getMessage(), 0, $e);
            }
        });
    }

    protected function getCacheExpiration(): \DateTimeInterface|\DateInterval|int
    {
        $cacheMode = config('vies_lookup.cache_mode', 'hours');

        if ('week' === $cacheMode) {
            return now()->next('Sunday')->startOfDay();
        }

        $hours = (int) config('vies_lookup.cache_hours', self::DEFAULT_CACHE_HOURS);

        return now()->addHours($hours);
    }
}
