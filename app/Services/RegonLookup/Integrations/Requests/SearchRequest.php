<?php

namespace App\Services\RegonLookup\Integrations\Requests;

use App\Services\RegonLookup\DTOs\RegonLookupResultDTO;
use App\Services\RegonLookup\Exceptions\RegonLookupException;
use Saloon\Http\Response;

class SearchRequest extends BaseRegonRequest
{
    public function __construct(
        private readonly string $nip,
        private readonly string $regon
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/DaneSzukaj';
    }

    protected function defaultBody(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ns="http://CIS/BIR/PUBL/2014/07">
    <soap:Header>
        <ns:pKluczUzytkownika>{{sessionId}}</ns:pKluczUzytkownika>
    </soap:Header>
    <soap:Body>
        <ns:DaneSzukaj>
            <ns:pParametryWyszukiwania>
                <ns:Nip>' . $this->nip . '</ns:Nip>
                <ns:Regon>' . $this->regon . '</ns:Regon>
            </ns:pParametryWyszukiwania>
        </ns:DaneSzukaj>
    </soap:Body>
</soap:Envelope>';
    }

    public function createDtoFromResponse(Response $response): RegonLookupResultDTO
    {
        $data = $response->json();

        if (!isset($data['regon'])) {
            throw new RegonLookupException('Invalid response from REGON API: missing regon field');
        }

        return RegonLookupResultDTO::fromApiResponse($data);
    }
}
