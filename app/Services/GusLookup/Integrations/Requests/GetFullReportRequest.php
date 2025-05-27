<?php

namespace App\Services\GusLookup\Integrations\Requests;

use App\Services\GusLookup\DTOs\GusFullReportResultDTO;
use App\Services\GusLookup\Enums\GusReportName;
use App\Services\GusLookup\Exceptions\BusinessNotFoundException;
use Illuminate\Support\Str;
use Saloon\Enums\Method;
use Saloon\Http\Response;

class GetFullReportRequest extends BaseGusRequest
{
    protected Method $method = Method::POST;

    public function __construct(
        protected string $regon,
        protected GusReportName $reportName = GusReportName::BIR11OsPrawna,
    ) {
        parent::__construct();
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

    public function createDtoFromResponse(Response $response): GusFullReportResultDTO
    {
        if ($this->hasRequestFailed($response)) {
            throw new BusinessNotFoundException();
        }

        $raw = $response->xml()
            ->{'DanePobierzPelnyRaportResponse'}
            ->{'DanePobierzPelnyRaportResult'}
        ;

        $xml = simplexml_load_string($raw)->dane;

        return new GusFullReportResultDTO(
            regon: (string) $xml->xpath('praw_regon9')[0],
            nip: (string) $xml->xpath('praw_nip')[0],
            nipStatus: (string) $xml->xpath('praw_statusNip')[0] ?: null,
            name: (string) $xml->xpath('praw_nazwa')[0],
            shortName: (string) $xml->xpath('praw_nazwaSkrocona')[0] ?: null,
            registrationNumber: (string) $xml->xpath('praw_numerWRejestrzeEwidencji')[0],
            registrationDate: (string) $xml->xpath('praw_dataWpisuDoRejestruEwidencji')[0],
            establishmentDate: (string) $xml->xpath('praw_dataPowstania')[0],
            businessStartDate: (string) $xml->xpath('praw_dataRozpoczeciaDzialalnosci')[0],
            regonRegistrationDate: (string) $xml->xpath('praw_dataWpisuDoRegon')[0] ?: null,
            businessSuspensionDate: (string) $xml->xpath('praw_dataZawieszeniaDzialalnosci')[0] ?: null,
            businessResumptionDate: (string) $xml->xpath('praw_dataWznowieniaDzialalnosci')[0] ?: null,
            lastChangeDate: (string) $xml->xpath('praw_dataZaistnieniaZmiany')[0],
            businessEndDate: (string) $xml->xpath('praw_dataZakonczeniaDzialalnosci')[0] ?: null,
            regonDeletionDate: (string) $xml->xpath('praw_dataSkresleniaZRegon')[0] ?: null,
            bankruptcyDeclarationDate: (string) $xml->xpath('praw_dataOrzeczeniaOUpadlosci')[0] ?: null,
            bankruptcyEndDate: (string) $xml->xpath('praw_dataZakonczeniaPostepowaniaUpadlosciowego')[0] ?: null,
            countryCode: (string) $xml->xpath('praw_adSiedzKraj_Symbol')[0],
            provinceCode: (string) $xml->xpath('praw_adSiedzWojewodztwo_Symbol')[0],
            countyCode: (string) $xml->xpath('praw_adSiedzPowiat_Symbol')[0],
            municipalityCode: (string) $xml->xpath('praw_adSiedzGmina_Symbol')[0],
            postalCode: (string) $xml->xpath('praw_adSiedzKodPocztowy')[0],
            postalCityCode: (string) $xml->xpath('praw_adSiedzMiejscowoscPoczty_Symbol')[0],
            cityCode: (string) $xml->xpath('praw_adSiedzMiejscowosc_Symbol')[0],
            streetCode: (string) $xml->xpath('praw_adSiedzUlica_Symbol')[0],
            buildingNumber: (string) $xml->xpath('praw_adSiedzNumerNieruchomosci')[0],
            apartmentNumber: (string) $xml->xpath('praw_adSiedzNumerLokalu')[0] ?: null,
            unusualLocation: (string) $xml->xpath('praw_adSiedzNietypoweMiejsceLokalizacji')[0] ?: null,
            phoneNumber: (string) $xml->xpath('praw_numerTelefonu')[0],
            internalPhoneNumber: (string) $xml->xpath('praw_numerWewnetrznyTelefonu')[0] ?: null,
            faxNumber: (string) $xml->xpath('praw_numerFaksu')[0] ?: null,
            email: (string) $xml->xpath('praw_adresEmail')[0] ?: null,
            website: (string) $xml->xpath('praw_adresStronyinternetowej')[0] ?: null,
            countryName: (string) $xml->xpath('praw_adSiedzKraj_Nazwa')[0],
            provinceName: (string) $xml->xpath('praw_adSiedzWojewodztwo_Nazwa')[0],
            countyName: (string) $xml->xpath('praw_adSiedzPowiat_Nazwa')[0],
            municipalityName: (string) $xml->xpath('praw_adSiedzGmina_Nazwa')[0],
            cityName: (string) $xml->xpath('praw_adSiedzMiejscowosc_Nazwa')[0],
            postalCityName: (string) $xml->xpath('praw_adSiedzMiejscowoscPoczty_Nazwa')[0],
            streetName: (string) $xml->xpath('praw_adSiedzUlica_Nazwa')[0],
            legalFormCode: (string) $xml->xpath('praw_podstawowaFormaPrawna_Symbol')[0],
            detailedLegalFormCode: (string) $xml->xpath('praw_szczegolnaFormaPrawna_Symbol')[0],
            financingFormCode: (string) $xml->xpath('praw_formaFinansowania_Symbol')[0],
            ownershipFormCode: (string) $xml->xpath('praw_formaWlasnosci_Symbol')[0],
            foundingBodyCode: (string) $xml->xpath('praw_organZalozycielski_Symbol')[0] ?: null,
            registrationAuthorityCode: (string) $xml->xpath('praw_organRejestrowy_Symbol')[0],
            registryTypeCode: (string) $xml->xpath('praw_rodzajRejestruEwidencji_Symbol')[0],
            legalFormName: (string) $xml->xpath('praw_podstawowaFormaPrawna_Nazwa')[0],
            detailedLegalFormName: (string) $xml->xpath('praw_szczegolnaFormaPrawna_Nazwa')[0],
            financingFormName: (string) $xml->xpath('praw_formaFinansowania_Nazwa')[0],
            ownershipFormName: (string) $xml->xpath('praw_formaWlasnosci_Nazwa')[0],
            foundingBodyName: (string) $xml->xpath('praw_organZalozycielski_Nazwa')[0] ?: null,
            registrationAuthorityName: (string) $xml->xpath('praw_organRejestrowy_Nazwa')[0],
            registryTypeName: (string) $xml->xpath('praw_rodzajRejestruEwidencji_Nazwa')[0],
            localUnitsCount: (int) $xml->praw_liczbaJednLokalnych
        );
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
}
