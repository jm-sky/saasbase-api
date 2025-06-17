<?php

namespace App\Services\RegonLookup\DTOs;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Common\Enums\AddressType;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @property string $regon                     | key: praw_regon9                                    | Example: 140672685
 * @property string $nip                       | key: praw_nip                                       | Example: 1251402446
 * @property string $nipStatus                 | key: praw_statusNip                                 | Example:
 * @property string $name                      | key: praw_nazwa                                     | Example: SKŁODOWSCY SPÓŁKA Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ
 * @property string $shortName                 | key: praw_nazwaSkrocona                             | Example:
 * @property string $registrationNumber        | key: praw_numerWRejestrzeEwidencji                  | Example: 0000264193
 * @property string $registrationDate          | key: praw_dataWpisuDoRejestruEwidencji              | Example: 2006-09-25
 * @property string $establishmentDate         | key: praw_dataPowstania                             | Example: 2006-09-11
 * @property string $businessStartDate         | key: praw_dataRozpoczeciaDzialalnosci               | Example: 2006-09-11
 * @property string $regonRegistrationDate     | key: praw_dataWpisuDoRegon                          | Example:
 * @property string $businessSuspensionDate    | key: praw_dataZawieszeniaDzialalnosci               | Example:
 * @property string $businessResumptionDate    | key: praw_dataWznowieniaDzialalnosci                | Example:
 * @property string $lastChangeDate            | key: praw_dataZaistnieniaZmiany                     | Example: 2024-03-27
 * @property string $businessEndDate           | key: praw_dataZakonczeniaDzialalnosci               | Example:
 * @property string $regonDeletionDate         | key: praw_dataSkresleniaZRegon                      | Example:
 * @property string $bankruptcyDeclarationDate | key: praw_dataOrzeczeniaOUpadlosci                  | Example:
 * @property string $bankruptcyEndDate         | key: praw_dataZakonczeniaPostepowaniaUpadlosciowego | Example:
 * @property string $countryCode               | key: praw_adSiedzKraj_Symbol                        | Example: PL
 * @property string $provinceCode              | key: praw_adSiedzWojewodztwo_Symbol                 | Example: 14
 * @property string $countyCode                | key: praw_adSiedzPowiat_Symbol                      | Example: 34
 * @property string $municipalityCode          | key: praw_adSiedzGmina_Symbol                       | Example: 021
 * @property string $postalCode                | key: praw_adSiedzKodPocztowy                        | Example: 05270
 * @property string $postalCityCode            | key: praw_adSiedzMiejscowoscPoczty_Symbol           | Example: 0920901
 * @property string $cityCode                  | key: praw_adSiedzMiejscowosc_Symbol                 | Example: 0920901
 * @property string $streetCode                | key: praw_adSiedzUlica_Symbol                       | Example: 09582
 * @property string $buildingNumber            | key: praw_adSiedzNumerNieruchomosci                 | Example: 43
 * @property string $apartmentNumber           | key: praw_adSiedzNumerLokalu                        | Example:
 * @property string $unusualLocation           | key: praw_adSiedzNietypoweMiejsceLokalizacji        | Example:
 * @property string $phoneNumber               | key: praw_numerTelefonu                             | Example: 0222426007
 * @property string $internalPhoneNumber       | key: praw_numerWewnetrznyTelefonu                   | Example:
 * @property string $faxNumber                 | key: praw_numerFaksu                                | Example:
 * @property string $email                     | key: praw_adresEmail                                | Example:
 * @property string $website                   | key: praw_adresStronyinternetowej                   | Example:
 * @property string $countryName               | key: praw_adSiedzKraj_Nazwa                         | Example: POLSKA
 * @property string $provinceName              | key: praw_adSiedzWojewodztwo_Nazwa                  | Example: MAZOWIECKIE
 * @property string $countyName                | key: praw_adSiedzPowiat_Nazwa                       | Example: wołomiński
 * @property string $municipalityName          | key: praw_adSiedzGmina_Nazwa                        | Example: Marki
 * @property string $cityName                  | key: praw_adSiedzMiejscowosc_Nazwa                  | Example: Marki
 * @property string $postalCityName            | key: praw_adSiedzMiejscowoscPoczty_Nazwa            | Example: Marki
 * @property string $streetName                | key: praw_adSiedzUlica_Nazwa                        | Example: ul. Tadeusza Kościuszki
 * @property string $legalFormCode             | key: praw_podstawowaFormaPrawna_Symbol              | Example: 1
 * @property string $detailedLegalFormCode     | key: praw_szczegolnaFormaPrawna_Symbol              | Example: 117
 * @property string $financingFormCode         | key: praw_formaFinansowania_Symbol                  | Example: 1
 * @property string $ownershipFormCode         | key: praw_formaWlasnosci_Symbol                     | Example: 214
 * @property string $foundingBodyCode          | key: praw_organZalozycielski_Symbol                 | Example:
 * @property string $registrationAuthorityCode | key: praw_organRejestrowy_Symbol                    | Example: 071010060
 * @property string $registryTypeCode          | key: praw_rodzajRejestruEwidencji_Symbol            | Example: 138
 * @property string $legalFormName             | key: praw_podstawowaFormaPrawna_Nazwa               | Example: OSOBA PRAWNA
 * @property string $detailedLegalFormName     | key: praw_szczegolnaFormaPrawna_Nazwa               | Example: SPÓŁKI Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ
 * @property string $financingFormName         | key: praw_formaFinansowania_Nazwa                   | Example: JEDNOSTKA SAMOFINANSUJĄCA NIE BĘDĄCA JEDNOSTKĄ BUDŻETOWĄ LUB SAMORZĄDOWYM ZAKŁADEM BUDŻETOWYM
 * @property string $ownershipFormName         | key: praw_formaWlasnosci_Nazwa                      | Example: WŁASNOŚĆ KRAJOWYCH OSÓB FIZYCZNYCH
 * @property string $foundingBodyName          | key: praw_organZalozycielski_Nazwa                  | Example:
 * @property string $registrationAuthorityName | key: praw_organRejestrowy_Nazwa                     | Example: SĄD REJONOWY DLA M.ST.WARSZAWY W WARSZAWIE,XIV WYDZIAŁ GOSPODARCZY KRAJOWEGO REJESTRU SĄDOWEGO
 * @property string $registryTypeName          | key: praw_rodzajRejestruEwidencji_Nazwa             | Example: REJESTR PRZEDSIĘBIORCÓW
 * @property string $localUnitsCount           | key: praw_liczbaJednLokalnych                       | Example: 0
 */
final class RegonReportForLegalPerson implements Arrayable, \JsonSerializable
{
    public function __construct(
        public readonly string $regon,
        public readonly string $nip,
        public readonly ?string $nipStatus,
        public readonly string $name,
        public readonly ?string $shortName,
        public readonly string $registrationNumber,
        public readonly string $registrationDate,
        public readonly string $establishmentDate,
        public readonly string $businessStartDate,
        public readonly ?string $regonRegistrationDate,
        public readonly ?string $businessSuspensionDate,
        public readonly ?string $businessResumptionDate,
        public readonly string $lastChangeDate,
        public readonly ?string $businessEndDate,
        public readonly ?string $regonDeletionDate,
        public readonly ?string $bankruptcyDeclarationDate,
        public readonly ?string $bankruptcyEndDate,
        public readonly string $countryCode,
        public readonly string $provinceCode,
        public readonly string $countyCode,
        public readonly string $municipalityCode,
        public readonly string $postalCode,
        public readonly string $postalCityCode,
        public readonly string $cityCode,
        public readonly string $streetCode,
        public readonly string $buildingNumber,
        public readonly ?string $apartmentNumber,
        public readonly ?string $unusualLocation,
        public readonly string $phoneNumber,
        public readonly ?string $internalPhoneNumber,
        public readonly ?string $faxNumber,
        public readonly ?string $email,
        public readonly ?string $website,
        public readonly string $countryName,
        public readonly string $provinceName,
        public readonly string $countyName,
        public readonly string $municipalityName,
        public readonly string $cityName,
        public readonly string $postalCityName,
        public readonly string $streetName,
        public readonly string $legalFormCode,
        public readonly string $detailedLegalFormCode,
        public readonly string $financingFormCode,
        public readonly string $ownershipFormCode,
        public readonly ?string $foundingBodyCode,
        public readonly string $registrationAuthorityCode,
        public readonly string $registryTypeCode,
        public readonly string $legalFormName,
        public readonly string $detailedLegalFormName,
        public readonly string $financingFormName,
        public readonly string $ownershipFormName,
        public readonly ?string $foundingBodyName,
        public readonly string $registrationAuthorityName,
        public readonly string $registryTypeName,
        public readonly int $localUnitsCount,
    ) {
    }

    public static function fromXml(\SimpleXMLElement $xml): static
    {
        return new self(
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

    public function toArray(): array
    {
        return [
            'regon'                     => $this->regon,
            'nip'                       => $this->nip,
            'nipStatus'                 => $this->nipStatus,
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

    public function getAddressAsString(): string
    {
        return implode(', ', array_filter([
            $this->cityName,
            $this->streetName,
            $this->buildingNumber,
            $this->apartmentNumber,
            $this->municipalityName,
            $this->provinceName,
            $this->countyName,
        ]));
    }

    public function toCommonLookupData(): CommonCompanyLookupData
    {
        $address = new AddressDTO(
            country: $this->countryCode,
            city: $this->cityName,
            type: AddressType::REGISTERED_OFFICE,
            isDefault: true,
            street: $this->streetName,
            building: $this->buildingNumber,
            flat: $this->apartmentNumber,
            postalCode: $this->postalCode
        );

        return new CommonCompanyLookupData(
            name: $this->name,
            country: $this->countryCode,
            vatId: $this->nip,
            regon: $this->regon,
            shortName: $this->shortName,
            phoneNumber: $this->phoneNumber,
            email: $this->email,
            website: $this->website,
            address: $address,
            bankAccount: null // REGON doesn't provide bank account information
        );
    }

    public function toUnifiedReportDto(): RegonReportUnified
    {
        return new RegonReportUnified(
            regon: $this->regon,
            nip: $this->nip,
            nipStatus: $this->nipStatus,
            name: $this->name,
            shortName: $this->shortName,
            registrationNumber: $this->registrationNumber,
            registrationDate: $this->registrationDate,
            establishmentDate: $this->establishmentDate,
            businessStartDate: $this->businessStartDate,
            regonRegistrationDate: $this->regonRegistrationDate,
            businessSuspensionDate: $this->businessSuspensionDate,
            businessResumptionDate: $this->businessResumptionDate,
            lastChangeDate: $this->lastChangeDate,
            businessEndDate: $this->businessEndDate,
            regonDeletionDate: $this->regonDeletionDate,
            bankruptcyDeclarationDate: $this->bankruptcyDeclarationDate,
            bankruptcyEndDate: $this->bankruptcyEndDate,
            countryCode: $this->countryCode,
            provinceCode: $this->provinceCode,
            countyCode: $this->countyCode,
            municipalityCode: $this->municipalityCode,
            postalCode: $this->postalCode,
            postalCityCode: $this->postalCityCode,
            cityCode: $this->cityCode,
            streetCode: $this->streetCode,
            buildingNumber: $this->buildingNumber,
            apartmentNumber: $this->apartmentNumber,
            unusualLocation: $this->unusualLocation,
            phoneNumber: $this->phoneNumber,
            internalPhoneNumber: $this->internalPhoneNumber,
            faxNumber: $this->faxNumber,
            email: $this->email,
            website: $this->website,
            countryName: $this->countryName,
            provinceName: $this->provinceName,
            countyName: $this->countyName,
            municipalityName: $this->municipalityName,
            cityName: $this->cityName,
            postalCityName: $this->postalCityName,
            streetName: $this->streetName,
            registrationDeletionDate: null, // Legal persons don't have this field
            registrationAuthorityCode: $this->registrationAuthorityCode,
            registryTypeCode: $this->registryTypeCode,
            registrationAuthorityName: $this->registrationAuthorityName,
            registryTypeName: $this->registryTypeName,
            legalFormCode: $this->legalFormCode,
            detailedLegalFormCode: $this->detailedLegalFormCode,
            financingFormCode: $this->financingFormCode,
            ownershipFormCode: $this->ownershipFormCode,
            foundingBodyCode: $this->foundingBodyCode,
            legalFormName: $this->legalFormName,
            detailedLegalFormName: $this->detailedLegalFormName,
            financingFormName: $this->financingFormName,
            ownershipFormName: $this->ownershipFormName,
            foundingBodyName: $this->foundingBodyName,
            localUnitsCount: $this->localUnitsCount,
            hasNotStartedActivity: null, // Legal persons don't have this field
        );
    }
}
