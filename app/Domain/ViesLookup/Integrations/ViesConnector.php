<?php

namespace App\Domain\ViesLookup\Integrations;

use Saloon\Http\Connector;

class ViesConnector extends Connector
{
    public function resolveBaseUrl(): string
    {
        return 'https://ec.europa.eu/taxation_customs/vies/services/checkVatService'; // This is SOAP
     }
}
