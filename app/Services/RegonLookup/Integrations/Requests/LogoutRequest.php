<?php

namespace App\Services\RegonLookup\Integrations\Requests;

use App\Services\RegonLookup\Exceptions\RegonLookupException;
use Saloon\Http\Response;

class LogoutRequest extends BaseRegonRequest
{
    public function resolveEndpoint(): string
    {
        return '/Wyloguj';
    }

    protected function defaultBody(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ns="http://CIS/BIR/PUBL/2014/07">
    <soap:Header>
        <ns:pKluczUzytkownika>{{sessionId}}</ns:pKluczUzytkownika>
    </soap:Header>
    <soap:Body>
        <ns:Wyloguj>
            <ns:pIdentyfikatorSesji>{{sessionId}}</ns:pIdentyfikatorSesji>
        </ns:Wyloguj>
    </soap:Body>
</soap:Envelope>';
    }

    public function createDtoFromResponse(Response $response): bool
    {
        $data = $response->json();

        if (!isset($data['success'])) {
            throw new RegonLookupException('Invalid response from REGON API: missing success field');
        }

        return $data['success'];
    }
}
