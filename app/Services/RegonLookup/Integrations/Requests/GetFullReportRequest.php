<?php

namespace App\Services\RegonLookup\Integrations\Requests;

use App\Services\RegonLookup\DTOs\RegonFullReportResultDTO;
use App\Services\RegonLookup\Enums\RegonReportName;
use App\Services\RegonLookup\Exceptions\RegonLookupException;
use Saloon\Http\Response;

class GetFullReportRequest extends BaseRegonRequest
{
    public function __construct(
        private readonly string $regon,
        private readonly RegonReportName $reportName = RegonReportName::BIR11OsPrawna,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/DanePobierzPelnyRaport';
    }

    protected function defaultBody(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ns="http://CIS/BIR/PUBL/2014/07">
    <soap:Header>
        <ns:pKluczUzytkownika>{{sessionId}}</ns:pKluczUzytkownika>
    </soap:Header>
    <soap:Body>
        <ns:DanePobierzPelnyRaport>
            <ns:pRegon>' . $this->regon . '</ns:pRegon>
            <ns:pNazwaRaportu>' . $this->reportName->value . '</ns:pNazwaRaportu>
        </ns:DanePobierzPelnyRaport>
    </soap:Body>
</soap:Envelope>';
    }

    public function createDtoFromResponse(Response $response): RegonFullReportResultDTO
    {
        $data = $response->json();

        if (!isset($data['regon'])) {
            throw new RegonLookupException('Invalid response from REGON API: missing regon field');
        }

        return RegonFullReportResultDTO::fromApiResponse($data);
    }
}
