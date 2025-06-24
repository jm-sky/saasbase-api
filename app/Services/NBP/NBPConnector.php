<?php

namespace App\Services\NBP;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class NBPConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return 'https://api.nbp.pl/api';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }
}
