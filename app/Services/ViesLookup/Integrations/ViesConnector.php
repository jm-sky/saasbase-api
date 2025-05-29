<?php

namespace App\Services\ViesLookup\Integrations;

use App\Services\ViesLookup\Integrations\Responses\ViesResponse;
use Saloon\Http\Connector;

class ViesConnector extends Connector
{
    public const BASE_URL = 'http://ec.europa.eu/taxation_customs/vies/services/checkVatService';

    protected ?string $response = ViesResponse::class;

    public function resolveBaseUrl(): string
    {
        return config('vies_lookup.base_url', self::BASE_URL); // This is SOAP
    }

    public function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'text/xml',
            'Accept'       => 'text/xml; charset=UTF-8',
        ];
    }
}
