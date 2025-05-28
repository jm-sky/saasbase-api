<?php

declare(strict_types=1);

namespace App\Services\RegonLookup\Integrations\Requests;

use App\Services\RegonLookup\Exceptions\RegonLookupException;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasXmlBody;

abstract class BaseRegonRequest extends Request implements HasBody
{
    use HasXmlBody;

    protected Method $method = Method::POST;

    public function __construct()
    {
        $this->baseUrl = config('regon_lookup.api_url', 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc');
    }

    public function resolveEndpoint(): string
    {
        return '/api/v1/regon';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'text/xml;charset=UTF-8',
            'Accept'       => 'text/xml',
        ];
    }

    protected function defaultBody(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ns="http://CIS/BIR/PUBL/2014/07">
    <soap:Header>
        <ns:pKluczUzytkownika>{{sessionId}}</ns:pKluczUzytkownika>
    </soap:Header>
    <soap:Body>
        {{body}}
    </soap:Body>
</soap:Envelope>';
    }

    protected function defaultQuery(): array
    {
        return [];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout'         => 30,
            'connect_timeout' => 10,
        ];
    }

    protected function defaultException(): string
    {
        return RegonLookupException::class;
    }
}
