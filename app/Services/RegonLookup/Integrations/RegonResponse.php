<?php

namespace App\Services\RegonLookup\Integrations;

use Saloon\Http\Response;

class RegonResponse extends Response
{
    public function getData(): array
    {
        return $this->json();
    }
}
