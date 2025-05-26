<?php

namespace App\Services\GusLookup\Integrations\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchByNipRequest extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        protected string $nip,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction'   => 'http://CIS/BIR/PUBL/2014/07/IUslugaBIRzewnPubl/DaneSzukajPodmioty',
        ];
    }

    protected function defaultBodyAsString(): string
    {
        $baseUrl = config('gus_lookup.api_url', 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc');

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ns="http://CIS/BIR/PUBL/2014/07">
    <soap:Header xmlns:wsa="http://www.w3.org/2005/08/addressing">
        <wsa:Action>http://CIS/BIR/PUBL/2014/07/IUslugaBIRzewnPubl/DaneSzukajPodmioty</wsa:Action>
        <wsa:To>{$baseUrl}</wsa:To>
    </soap:Header>
    <soap:Body>
        <ns:DaneSzukajPodmioty>
            <ns:pParametryWyszukiwania>
                <ns:Nip>{$this->nip}</ns:Nip>
            </ns:pParametryWyszukiwania>
        </ns:DaneSzukajPodmioty>
    </soap:Body>
</soap:Envelope>
XML;
    }
}
