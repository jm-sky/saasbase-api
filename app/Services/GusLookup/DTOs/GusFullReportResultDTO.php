<?php

namespace App\Services\GusLookup\DTOs;

use Illuminate\Contracts\Support\Arrayable;

/**
 * <DanePobierzPelnyRaportResponse xmlns="http://CIS/BIR/PUBL/2014/07">
 * <DanePobierzPelnyRaportResult><root>&#xD;
 *  <dane></dane>&#xD;
 *    <praw_regon9>140672685</praw_regon9>&#xD;
 *    <praw_nip>1251402446</praw_nip>&#xD;
 *    <praw_statusNip />&#xD;
 *    <praw_nazwa>SKŁODOWSCY SPÓŁKA Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ</praw_nazwa>&#xD;
 *    <praw_nazwaSkrocona />&#xD;
 *    <praw_numerWRejestrzeEwidencji>0000264193</praw_numerWRejestrzeEwidencji>&#xD;
 *    <praw_dataWpisuDoRejestruEwidencji>2006-09-25</praw_dataWpisuDoRejestruEwidencji>&#xD;
 *    <praw_dataPowstania>2006-09-11</praw_dataPowstania>&#xD;
 *    <praw_dataRozpoczeciaDzialalnosci>2006-09-11</praw_dataRozpoczeciaDzialalnosci>&#xD;
 *    <praw_dataWpisuDoRegon />&#xD;
 *    <praw_dataZawieszeniaDzialalnosci />&#xD;
 *    <praw_dataWznowieniaDzialalnosci />&#xD;
 *    <praw_dataZaistnieniaZmiany>2024-03-27</praw_dataZaistnieniaZmiany>&#xD;
 *    <praw_dataZakonczeniaDzialalnosci />&#xD;
 *    <praw_dataSkresleniaZRegon />&#xD;
 *    <praw_dataOrzeczeniaOUpadlosci />&#xD;
 *    <praw_dataZakonczeniaPostepowaniaUpadlosciowego />&#xD;
 *    <praw_adSiedzKraj_Symbol>PL</praw_adSiedzKraj_Symbol>&#xD;
 *    <praw_adSiedzWojewodztwo_Symbol>14</praw_adSiedzWojewodztwo_Symbol>&#xD;
 *    <praw_adSiedzPowiat_Symbol>34</praw_adSiedzPowiat_Symbol>&#xD;
 *    <praw_adSiedzGmina_Symbol>021</praw_adSiedzGmina_Symbol>&#xD;
 *    <praw_adSiedzKodPocztowy>05270</praw_adSiedzKodPocztowy>&#xD;
 *    <praw_adSiedzMiejscowoscPoczty_Symbol>0920901</praw_adSiedzMiejscowoscPoczty_Symbol>&#xD;
 *    <praw_adSiedzMiejscowosc_Symbol>0920901</praw_adSiedzMiejscowosc_Symbol>&#xD;
 *    <praw_adSiedzUlica_Symbol>09582</praw_adSiedzUlica_Symbol>&#xD;
 *    <praw_adSiedzNumerNieruchomosci>43</praw_adSiedzNumerNieruchomosci>&#xD;
 *    <praw_adSiedzNumerLokalu />&#xD;
 *    <praw_adSiedzNietypoweMiejsceLokalizacji />&#xD;
 *    <praw_numerTelefonu>0222426007</praw_numerTelefonu>&#xD;
 *    <praw_numerWewnetrznyTelefonu />&#xD;
 *    <praw_numerFaksu />&#xD;
 *    <praw_adresEmail />&#xD;
 *    <praw_adresStronyinternetowej />&#xD;
 *    <praw_adSiedzKraj_Nazwa>POLSKA</praw_adSiedzKraj_Nazwa>&#xD;
 *    <praw_adSiedzWojewodztwo_Nazwa>MAZOWIECKIE</praw_adSiedzWojewodztwo_Nazwa>&#xD;
 *    <praw_adSiedzPowiat_Nazwa>wołomiński</praw_adSiedzPowiat_Nazwa>&#xD;
 *    <praw_adSiedzGmina_Nazwa>Marki</praw_adSiedzGmina_Nazwa>&#xD;
 *    <praw_adSiedzMiejscowosc_Nazwa>Marki</praw_adSiedzMiejscowosc_Nazwa>&#xD;
 *    <praw_adSiedzMiejscowoscPoczty_Nazwa>Marki</praw_adSiedzMiejscowoscPoczty_Nazwa>&#xD;
 *    <praw_adSiedzUlica_Nazwa>ul. Tadeusza Kościuszki</praw_adSiedzUlica_Nazwa>&#xD;
 *    <praw_podstawowaFormaPrawna_Symbol>1</praw_podstawowaFormaPrawna_Symbol>&#xD;
 *    <praw_szczegolnaFormaPrawna_Symbol>117</praw_szczegolnaFormaPrawna_Symbol>&#xD;
 *    <praw_formaFinansowania_Symbol>1</praw_formaFinansowania_Symbol>&#xD;
 *    <praw_formaWlasnosci_Symbol>214</praw_formaWlasnosci_Symbol>&#xD;
 *    <praw_organZalozycielski_Symbol />&#xD;
 *    <praw_organRejestrowy_Symbol>071010060</praw_organRejestrowy_Symbol>&#xD;
 *    <praw_rodzajRejestruEwidencji_Symbol>138</praw_rodzajRejestruEwidencji_Symbol>&#xD;
 *    <praw_podstawowaFormaPrawna_Nazwa>OSOBA PRAWNA</praw_podstawowaFormaPrawna_Nazwa>&#xD;
 *    <praw_szczegolnaFormaPrawna_Nazwa>SPÓŁKI Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ</praw_szczegolnaFormaPrawna_Nazwa>&#xD;
 *    <praw_formaFinansowania_Nazwa>JEDNOSTKA SAMOFINANSUJĄCA NIE BĘDĄCA JEDNOSTKĄ BUDŻETOWĄ LUB SAMORZĄDOWYM ZAKŁADEM BUDŻETOWYM</praw_formaFinansowania_Nazwa>&#xD;
 *    <praw_formaWlasnosci_Nazwa>WŁASNOŚĆ KRAJOWYCH OSÓB FIZYCZNYCH</praw_formaWlasnosci_Nazwa>&#xD;
 *    <praw_organZalozycielski_Nazwa />&#xD;
 *    <praw_organRejestrowy_Nazwa>SĄD REJONOWY DLA M.ST.WARSZAWY W WARSZAWIE,XIV WYDZIAŁ GOSPODARCZY KRAJOWEGO REJESTRU SĄDOWEGO</praw_organRejestrowy_Nazwa>&#xD;
 *    <praw_rodzajRejestruEwidencji_Nazwa>REJESTR PRZEDSIĘBIORCÓW</praw_rodzajRejestruEwidencji_Nazwa>&#xD;
 *    <praw_liczbaJednLokalnych>0</praw_liczbaJednLokalnych>&#xD;
 *  </dane>&#xD;
 *</root>
 *</DanePobierzPelnyRaportResult>
 *</DanePobierzPelnyRaportResponse>.
 */
class GusFullReportResultDTO implements Arrayable, \JsonSerializable
{
    public function __construct(
        public string $regon,
        public string $vatId,
        public ?string $vatIdStatus,
        public string $name,
        public ?string $shortName,
        public string $registrationNumber,
        public string $registrationDate,
        public string $establishmentDate,
        public string $businessStartDate,
        public ?string $regonRegistrationDate,
        public ?string $businessSuspensionDate,
        public ?string $businessResumptionDate,
        public string $lastChangeDate,
        public ?string $businessEndDate,
        public ?string $regonDeletionDate,
        public ?string $bankruptcyDeclarationDate,
        public ?string $bankruptcyEndDate,
        public string $countryCode,
        public string $provinceCode,
        public string $countyCode,
        public string $municipalityCode,
        public string $postalCode,
        public string $postalCityCode,
        public string $cityCode,
        public string $streetCode,
        public string $buildingNumber,
        public ?string $apartmentNumber,
        public ?string $unusualLocation,
        public string $phoneNumber,
        public ?string $internalPhoneNumber,
        public ?string $faxNumber,
        public ?string $email,
        public ?string $website,
        public string $countryName,
        public string $provinceName,
        public string $countyName,
        public string $municipalityName,
        public string $cityName,
        public string $postalCityName,
        public string $streetName,
        public string $legalFormCode,
        public string $detailedLegalFormCode,
        public string $financingFormCode,
        public string $ownershipFormCode,
        public ?string $foundingBodyCode,
        public string $registrationAuthorityCode,
        public string $registryTypeCode,
        public string $legalFormName,
        public string $detailedLegalFormName,
        public string $financingFormName,
        public string $ownershipFormName,
        public ?string $foundingBodyName,
        public string $registrationAuthorityName,
        public string $registryTypeName,
        public int $localUnitsCount,
    ) {
    }

    public function toArray(): array
    {
        return [
            'regon'                     => $this->regon,
            'vatId'                     => $this->vatId,
            'vatIdStatus'               => $this->vatIdStatus,
            'name'                      => $this->name,
            'shortName'                 => $this->shortName,
            'registrationNumber'        => $this->registrationNumber,
            'registrationDate'          => $this->registrationDate,
            'establishmentDate'         => $this->establishmentDate,
            'businessStartDate'         => $this->businessStartDate,
            'regonRegistrationDate'     => $this->regonRegistrationDate,
            'businessSuspensionDate'    => $this->businessSuspensionDate,
            'businessResumptionDate'    => $this->businessResumptionDate,
            'lastChangeDate'            => $this->lastChangeDate,
            'businessEndDate'           => $this->businessEndDate,
            'regonDeletionDate'         => $this->regonDeletionDate,
            'bankruptcyDeclarationDate' => $this->bankruptcyDeclarationDate,
            'bankruptcyEndDate'         => $this->bankruptcyEndDate,
            'countryCode'               => $this->countryCode,
            'provinceCode'              => $this->provinceCode,
            'countyCode'                => $this->countyCode,
            'municipalityCode'          => $this->municipalityCode,
            'postalCode'                => $this->postalCode,
            'postalCityCode'            => $this->postalCityCode,
            'cityCode'                  => $this->cityCode,
            'streetCode'                => $this->streetCode,
            'buildingNumber'            => $this->buildingNumber,
            'apartmentNumber'           => $this->apartmentNumber,
            'unusualLocation'           => $this->unusualLocation,
            'phoneNumber'               => $this->phoneNumber,
            'internalPhoneNumber'       => $this->internalPhoneNumber,
            'faxNumber'                 => $this->faxNumber,
            'email'                     => $this->email,
            'website'                   => $this->website,
            'countryName'               => $this->countryName,
            'provinceName'              => $this->provinceName,
            'countyName'                => $this->countyName,
            'municipalityName'          => $this->municipalityName,
            'cityName'                  => $this->cityName,
            'postalCityName'            => $this->postalCityName,
            'streetName'                => $this->streetName,
            'legalFormCode'             => $this->legalFormCode,
            'detailedLegalFormCode'     => $this->detailedLegalFormCode,
            'financingFormCode'         => $this->financingFormCode,
            'ownershipFormCode'         => $this->ownershipFormCode,
            'foundingBodyCode'          => $this->foundingBodyCode,
            'registrationAuthorityCode' => $this->registrationAuthorityCode,
            'registryTypeCode'          => $this->registryTypeCode,
            'legalFormName'             => $this->legalFormName,
            'detailedLegalFormName'     => $this->detailedLegalFormName,
            'financingFormName'         => $this->financingFormName,
            'ownershipFormName'         => $this->ownershipFormName,
            'foundingBodyName'          => $this->foundingBodyName,
            'registrationAuthorityName' => $this->registrationAuthorityName,
            'registryTypeName'          => $this->registryTypeName,
            'localUnitsCount'           => $this->localUnitsCount,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
