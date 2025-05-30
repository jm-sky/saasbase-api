<?php

namespace App\Services\IbanApi\Integrations;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class IbanApiConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return 'https://api.ibanapi.com/v1';
    }

    protected function defaultQuery(): array
    {
        return [
            'api_key' => config('services.ibanapi.key'),
        ];
    }
}
