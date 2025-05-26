<?php

namespace App\Services\GusLookup\Integrations;

use Saloon\Http\Connector;

class GusApiConnector extends Connector
{
    protected ?string $sessionId = null;

    public function resolveBaseUrl(): string
    {
        return config('gus_lookup.api_url', 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc');
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    protected function defaultHeaders(): array
    {
        $headers = [
            'Content-Type' => 'text/xml; charset=utf-8',
        ];

        if ($this->sessionId) {
            $headers['sid'] = $this->sessionId;
        }

        return $headers;
    }
}
