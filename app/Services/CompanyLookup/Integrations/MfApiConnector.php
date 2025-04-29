<?php

namespace App\Services\CompanyLookup\Integrations;

use Saloon\Http\Connector;

class MfApiConnector extends Connector
{
    public function resolveBaseUrl(): string
    {
        return 'https://wl-api.mf.gov.pl';
    }
}
