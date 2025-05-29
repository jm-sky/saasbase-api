<?php

namespace App\Services\RegonLookup\Integrations\Requests;

use App\Services\RegonLookup\DTOs\RegonReportForLegalPerson;
use App\Services\RegonLookup\DTOs\RegonReportForNaturalPerson;
use App\Services\RegonLookup\Enums\RegonReportName;
use App\Services\RegonLookup\Exceptions\BusinessNotFoundException;
use App\Services\RegonLookup\Exceptions\ReportNotImplementedException;
use Illuminate\Support\Str;
use Saloon\Http\Response;

class GetFullReportRequest extends BaseRegonRequest
{
    public function __construct(
        private readonly string $regon,
        private readonly RegonReportName $reportName = RegonReportName::BIR11LegalPerson,
        private readonly ?string $nip = null,
    ) {
        parent::__construct();
    }

    public function defaultBody(): ?string
    {
        return <<<XML
            <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ns="http://CIS/BIR/PUBL/2014/07">
               <soap:Header xmlns:wsa="http://www.w3.org/2005/08/addressing">
                  <wsa:To>{$this->baseUrl}</wsa:To>
                  <wsa:Action>http://CIS/BIR/PUBL/2014/07/IUslugaBIRzewnPubl/DanePobierzPelnyRaport</wsa:Action>
               </soap:Header>
               <soap:Body>
                  <ns:DanePobierzPelnyRaport>
                    <ns:pRegon>{$this->regon}</ns:pRegon>
                    <ns:pNazwaRaportu>{$this->reportName->value}</ns:pNazwaRaportu>
                  </ns:DanePobierzPelnyRaport>
               </soap:Body>
            </soap:Envelope>
        XML;
    }

    public function hasRequestFailed(Response $response): ?bool
    {
        if (Str::contains($response->body(), 'ErrorCode')) {
            return true;
        }

        $raw = (string) $response->xml()
            ->{'DanePobierzPelnyRaportResponse'}
            ->{'DanePobierzPelnyRaportResult'}
        ;

        return empty($raw);
    }

    public function createDtoFromResponse(Response $response): RegonReportForNaturalPerson|RegonReportForLegalPerson
    {
        if ($this->hasRequestFailed($response)) {
            throw new BusinessNotFoundException();
        }

        $raw = $response->xml()
            ->{'DanePobierzPelnyRaportResponse'}
            ->{'DanePobierzPelnyRaportResult'}
        ;

        $xml = simplexml_load_string($raw)->dane;

        switch ($this->reportName) {
            case RegonReportName::BIR11NaturalPersonCeidg:
                return RegonReportForNaturalPerson::fromXml($xml, $this->nip);
            case RegonReportName::BIR11LegalPerson:
                return RegonReportForLegalPerson::fromXml($xml);
            default:
                throw ReportNotImplementedException::forReport($this->reportName);
        }
    }
}
