<?php

namespace App\Domain\CompanyLookup\Services;

use App\Domain\CompanyLookup\DTOs\CompanyLookupResultDTO;
use App\Domain\CompanyLookup\Integrations\MfApiConnector;
use App\Domain\CompanyLookup\Integrations\Requests\SearchByNipRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CompanyLookupService
{
    protected MfApiConnector $connector;

    public function __construct()
    {
        $this->connector = new MfApiConnector();
    }

    public function findByNip(string $nip): ?CompanyLookupResultDTO
    {
        $nip = preg_replace('/[^0-9]/', '', $nip); // Czyścimy NIP z myślników itp.

        return Cache::remember("company_lookup_nip_{$nip}", now()->addHours(6), function () use ($nip) {
            try {
                $request = new SearchByNipRequest($nip);
                $response = $this->connector->send($request);

                if ($response->successful()) {
                    $subject = $response->json('result.subject');

                    if ($subject) {
                        return CompanyLookupResultDTO::fromApiResponse($subject);
                    }
                }

                return null;
            } catch (\Throwable $e) {
                Log::error('CompanyLookupService error: ' . $e->getMessage(), [
                    'nip' => $nip,
                ]);

                return null;
            }
        });
    }
}
