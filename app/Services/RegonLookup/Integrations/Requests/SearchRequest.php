<?php

namespace App\Services\RegonLookup\Integrations\Requests;

use App\Services\RegonLookup\DTOs\RegonLookupResultDTO;
use App\Services\RegonLookup\Enums\EntityType;
use Illuminate\Support\Str;
use Saloon\Http\Response;

class SearchRequest extends BaseRegonRequest
{
    public function __construct(
        protected readonly string $nip,
        protected readonly string $regon
    ) {
        parent::__construct();
    }

    public function hasRequestFailed(Response $response): ?bool
    {
        if (Str::contains($response->body(), 'ErrorCode')) {
            return true;
        }

        $raw = (string) $response->xml()
            ->{'DaneSzukajPodmiotyResponse'}
            ->{'DaneSzukajPodmiotyResult'}
        ;

        return empty($raw);
    }

    protected function defaultBody(): string
    {
        return <<<XML
            <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ns="http://CIS/BIR/PUBL/2014/07" xmlns:dat="http://CIS/BIR/PUBL/2014/07/DataContract">
               <soap:Header xmlns:wsa="http://www.w3.org/2005/08/addressing">
                  <wsa:Action>http://CIS/BIR/PUBL/2014/07/IUslugaBIRzewnPubl/DaneSzukajPodmioty</wsa:Action>
                  <wsa:To>{$this->baseUrl}</wsa:To>
               </soap:Header>
               <soap:Body>
                  <ns:DaneSzukajPodmioty>
                     <ns:pParametryWyszukiwania>
                        <dat:Nip>{$this->nip}</dat:Nip>
                        <dat:Regon>{$this->regon}</dat:Regon>
                     </ns:pParametryWyszukiwania>
                  </ns:DaneSzukajPodmioty>
               </soap:Body>
            </soap:Envelope>
        XML;
    }

    public function createDtoFromResponse(Response $response): RegonLookupResultDTO
    {
        $raw = $response->xml()
            ->{'DaneSzukajPodmiotyResponse'}
            ->{'DaneSzukajPodmiotyResult'}
        ;

        $xml = simplexml_load_string($raw);

        return new RegonLookupResultDTO(
            name: (string) $xml->xpath('//Nazwa')[0],
            regon: (string) $xml->xpath('//Regon')[0],
            nip: (string) $xml->xpath('//Nip')[0],
            type: EntityType::from((string) $xml->xpath('//Typ')[0]),
            statusNip: (string) $xml->xpath('//StatusNip')[0],
            dateOfEnd: (string) $xml->xpath('//DataZakonczeniaDzialalnosci')[0],
            voivodeship: (string) $xml->xpath('//Wojewodztwo')[0],
            province: (string) $xml->xpath('//Powiat')[0],
            community: (string) $xml->xpath('//Gmina')[0],
            city: (string) $xml->xpath('//Miejscowosc')[0],
            postalCode: (string) $xml->xpath('//KodPocztowy')[0],
            street: (string) $xml->xpath('//Ulica')[0],
            building: (string) $xml->xpath('//NrNieruchomosci')[0],
            flat: (string) $xml->xpath('//NrLokalu')[0],
            silosId: (string) $xml->xpath('//SilosID')[0],
        );
    }
}
