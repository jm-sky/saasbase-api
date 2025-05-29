<?php

namespace App\Services\RegonLookup\DTOs;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Common\Enums\AddressType;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @property string  $regon                     | Example: 140672685
 * @property ?string $nip                       | Example: 1251402446
 * @property ?string $nipStatus                 | Example:
 * @property string  $name                      | Example: SKŁODOWSCY SPÓŁKA Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ
 * @property ?string $shortName                 | Example:
 * @property string  $registrationNumber        | Example: 0000264193
 * @property string  $registrationDate          | Example: 2006-09-25
 * @property string  $establishmentDate         | Example: 2006-09-11
 * @property string  $businessStartDate         | Example: 2006-09-11
 * @property ?string $regonRegistrationDate     | Example:
 * @property ?string $businessSuspensionDate    | Example:
 * @property ?string $businessResumptionDate    | Example:
 * @property string  $lastChangeDate            | Example: 2024-03-27
 * @property ?string $businessEndDate           | Example:
 * @property ?string $regonDeletionDate         | Example:
 * @property ?string $bankruptcyDeclarationDate | Example:
 * @property ?string $bankruptcyEndDate         | Example:
 * @property string  $countryCode               | Example: PL
 * @property string  $provinceCode              | Example: 14
 * @property string  $countyCode                | Example: 34
 * @property string  $municipalityCode          | Example: 021
 * @property string  $postalCode                | Example: 05270
 * @property string  $postalCityCode            | Example: 0920901
 * @property string  $cityCode                  | Example: 0920901
 * @property string  $streetCode                | Example: 09582
 * @property string  $buildingNumber            | Example: 43
 * @property ?string $apartmentNumber           | Example:
 * @property ?string $unusualLocation           | Example:
 * @property ?string $phoneNumber               | Example: 0222426007
 * @property ?string $internalPhoneNumber       | Example:
 * @property ?string $faxNumber                 | Example:
 * @property ?string $email                     | Example:
 * @property ?string $website                   | Example:
 * @property string  $countryName               | Example: POLSKA
 * @property string  $provinceName              | Example: MAZOWIECKIE
 * @property string  $countyName                | Example: wołomiński
 * @property string  $municipalityName          | Example: Marki
 * @property string  $cityName                  | Example: Marki
 * @property string  $postalCityName            | Example: Marki
 * @property string  $streetName                | Example: ul. Tadeusza Kościuszki
 * @property ?string $registrationDeletionDate  | Example:
 * @property string  $registrationAuthorityCode | Example: 071010060
 * @property string  $registryTypeCode          | Example: 138
 * @property string  $registrationAuthorityName | Example: SĄD REJONOWY DLA M.ST.WARSZAWY W WARSZAWIE
 * @property string  $registryTypeName          | Example: REJESTR PRZEDSIĘBIORCÓW
 * @property ?string $legalFormCode             | Example: 1
 * @property ?string $detailedLegalFormCode     | Example: 117
 * @property ?string $financingFormCode         | Example: 1
 * @property ?string $ownershipFormCode         | Example: 214
 * @property ?string $foundingBodyCode          | Example:
 * @property ?string $legalFormName             | Example: OSOBA PRAWNA
 * @property ?string $detailedLegalFormName     | Example: SPÓŁKI Z OGRANICZONĄ ODPOWIEDZIALNOŚCIĄ
 * @property ?string $financingFormName         | Example: JEDNOSTKA SAMOFINANSUJĄCA
 * @property ?string $ownershipFormName         | Example: WŁASNOŚĆ KRAJOWYCH OSÓB FIZYCZNYCH
 * @property ?string $foundingBodyName          | Example:
 * @property ?int    $localUnitsCount           | Example: 0
 * @property ?bool   $hasNotStartedActivity     | Example: false
 * @property ?bool   $cache
 */
class RegonReportUnified implements Arrayable, \JsonSerializable
{
    public function __construct(
        public readonly string $regon,
        public readonly ?string $nip,
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
        public readonly ?string $phoneNumber,
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
        public readonly ?string $registrationDeletionDate,
        public readonly string $registrationAuthorityCode,
        public readonly string $registryTypeCode,
        public readonly string $registrationAuthorityName,
        public readonly string $registryTypeName,
        public readonly ?string $legalFormCode,
        public readonly ?string $detailedLegalFormCode,
        public readonly ?string $financingFormCode,
        public readonly ?string $ownershipFormCode,
        public readonly ?string $foundingBodyCode,
        public readonly ?string $legalFormName,
        public readonly ?string $detailedLegalFormName,
        public readonly ?string $financingFormName,
        public readonly ?string $ownershipFormName,
        public readonly ?string $foundingBodyName,
        public readonly ?int $localUnitsCount,
        public readonly ?bool $hasNotStartedActivity,
        public readonly ?bool $cache = null,
    ) {
    }

    public static function fromXml(\SimpleXMLElement $xml): static
    {
        return new static(
            regon: (string) $xml->xpath('praw_regon9|fiz_regon9')[0],
            nip: (string) $xml->xpath('praw_nip')[0] ?: null,
            nipStatus: (string) $xml->xpath('praw_statusNip')[0] ?: null,
            name: (string) $xml->xpath('praw_nazwa|fiz_nazwa')[0],
            shortName: (string) $xml->xpath('praw_nazwaSkrocona|fiz_nazwaSkrocona')[0] ?: null,
            registrationNumber: (string) $xml->xpath('praw_numerWRejestrzeEwidencji|fizC_numerWRejestrzeEwidencji')[0],
            registrationDate: (string) $xml->xpath('praw_dataWpisuDoRejestruEwidencji|fizC_dataWpisuDoRejestruEwidencji')[0],
            establishmentDate: (string) $xml->xpath('praw_dataPowstania|fiz_dataPowstania')[0],
            businessStartDate: (string) $xml->xpath('praw_dataRozpoczeciaDzialalnosci|fiz_dataRozpoczeciaDzialalnosci')[0],
            regonRegistrationDate: (string) $xml->xpath('praw_dataWpisuDoRegon|fiz_dataWpisuDzialalnosciDoRegon')[0] ?: null,
            businessSuspensionDate: (string) $xml->xpath('praw_dataZawieszeniaDzialalnosci|fiz_dataZawieszeniaDzialalnosci')[0] ?: null,
            businessResumptionDate: (string) $xml->xpath('praw_dataWznowieniaDzialalnosci|fiz_dataWznowieniaDzialalnosci')[0] ?: null,
            lastChangeDate: (string) $xml->xpath('praw_dataZaistnieniaZmiany|fiz_dataZaistnieniaZmianyDzialalnosci')[0],
            businessEndDate: (string) $xml->xpath('praw_dataZakonczeniaDzialalnosci|fiz_dataZakonczeniaDzialalnosci')[0] ?: null,
            regonDeletionDate: (string) $xml->xpath('praw_dataSkresleniaZRegon|fiz_dataSkresleniaDzialalnosciZRegon')[0] ?: null,
            bankruptcyDeclarationDate: (string) $xml->xpath('praw_dataOrzeczeniaOUpadlosci|fiz_dataOrzeczeniaOUpadlosci')[0] ?: null,
            bankruptcyEndDate: (string) $xml->xpath('praw_dataZakonczeniaPostepowaniaUpadlosciowego|fiz_dataZakonczeniaPostepowaniaUpadlosciowego')[0] ?: null,
            countryCode: (string) $xml->xpath('praw_adSiedzKraj_Symbol|fiz_adSiedzKraj_Symbol')[0],
            provinceCode: (string) $xml->xpath('praw_adSiedzWojewodztwo_Symbol|fiz_adSiedzWojewodztwo_Symbol')[0],
            countyCode: (string) $xml->xpath('praw_adSiedzPowiat_Symbol|fiz_adSiedzPowiat_Symbol')[0],
            municipalityCode: (string) $xml->xpath('praw_adSiedzGmina_Symbol|fiz_adSiedzGmina_Symbol')[0],
            postalCode: (string) $xml->xpath('praw_adSiedzKodPocztowy|fiz_adSiedzKodPocztowy')[0],
            postalCityCode: (string) $xml->xpath('praw_adSiedzMiejscowoscPoczty_Symbol|fiz_adSiedzMiejscowoscPoczty_Symbol')[0],
            cityCode: (string) $xml->xpath('praw_adSiedzMiejscowosc_Symbol|fiz_adSiedzMiejscowosc_Symbol')[0],
            streetCode: (string) $xml->xpath('praw_adSiedzUlica_Symbol|fiz_adSiedzUlica_Symbol')[0],
            buildingNumber: (string) $xml->xpath('praw_adSiedzNumerNieruchomosci|fiz_adSiedzNumerNieruchomosci')[0],
            apartmentNumber: (string) $xml->xpath('praw_adSiedzNumerLokalu|fiz_adSiedzNumerLokalu')[0] ?: null,
            unusualLocation: (string) $xml->xpath('praw_adSiedzNietypoweMiejsceLokalizacji|fiz_adSiedzNietypoweMiejsceLokalizacji')[0] ?: null,
            phoneNumber: (string) $xml->xpath('praw_numerTelefonu|fiz_numerTelefonu')[0] ?: null,
            internalPhoneNumber: (string) $xml->xpath('praw_numerWewnetrznyTelefonu|fiz_numerWewnetrznyTelefonu')[0] ?: null,
            faxNumber: (string) $xml->xpath('praw_numerFaksu|fiz_numerFaksu')[0] ?: null,
            email: (string) $xml->xpath('praw_adresEmail|fiz_adresEmail')[0] ?: null,
            website: (string) $xml->xpath('praw_adresStronyinternetowej|fiz_adresStronyinternetowej')[0] ?: null,
            countryName: (string) $xml->xpath('praw_adSiedzKraj_Nazwa|fiz_adSiedzKraj_Nazwa')[0],
            provinceName: (string) $xml->xpath('praw_adSiedzWojewodztwo_Nazwa|fiz_adSiedzWojewodztwo_Nazwa')[0],
            countyName: (string) $xml->xpath('praw_adSiedzPowiat_Nazwa|fiz_adSiedzPowiat_Nazwa')[0],
            municipalityName: (string) $xml->xpath('praw_adSiedzGmina_Nazwa|fiz_adSiedzGmina_Nazwa')[0],
            cityName: (string) $xml->xpath('praw_adSiedzMiejscowosc_Nazwa|fiz_adSiedzMiejscowosc_Nazwa')[0],
            postalCityName: (string) $xml->xpath('praw_adSiedzMiejscowoscPoczty_Nazwa|fiz_adSiedzMiejscowoscPoczty_Nazwa')[0],
            streetName: (string) $xml->xpath('praw_adSiedzUlica_Nazwa|fiz_adSiedzUlica_Nazwa')[0],
            registrationDeletionDate: (string) $xml->xpath('praw_dataSkresleniaZRejestruEwidencji|fizC_dataSkresleniaZRejestruEwidencji')[0] ?: null,
            registrationAuthorityCode: (string) $xml->xpath('praw_organRejestrowy_Symbol|fizC_OrganRejestrowy_Symbol')[0],
            registryTypeCode: (string) $xml->xpath('praw_rodzajRejestruEwidencji_Symbol|fizC_RodzajRejestru_Symbol')[0],
            registrationAuthorityName: (string) $xml->xpath('praw_organRejestrowy_Nazwa|fizC_OrganRejestrowy_Nazwa')[0],
            registryTypeName: (string) $xml->xpath('praw_rodzajRejestruEwidencji_Nazwa|fizC_RodzajRejestru_Nazwa')[0],
            legalFormCode: (string) $xml->xpath('praw_podstawowaFormaPrawna_Symbol')[0] ?: null,
            detailedLegalFormCode: (string) $xml->xpath('praw_szczegolnaFormaPrawna_Symbol')[0] ?: null,
            financingFormCode: (string) $xml->xpath('praw_formaFinansowania_Symbol')[0] ?: null,
            ownershipFormCode: (string) $xml->xpath('praw_formaWlasnosci_Symbol')[0] ?: null,
            foundingBodyCode: (string) $xml->xpath('praw_organZalozycielski_Symbol')[0] ?: null,
            legalFormName: (string) $xml->xpath('praw_podstawowaFormaPrawna_Nazwa')[0] ?: null,
            detailedLegalFormName: (string) $xml->xpath('praw_szczegolnaFormaPrawna_Nazwa')[0] ?: null,
            financingFormName: (string) $xml->xpath('praw_formaFinansowania_Nazwa')[0] ?: null,
            ownershipFormName: (string) $xml->xpath('praw_formaWlasnosci_Nazwa')[0] ?: null,
            foundingBodyName: (string) $xml->xpath('praw_organZalozycielski_Nazwa')[0] ?: null,
            localUnitsCount: (int) $xml->xpath('praw_liczbaJednLokalnych')[0] ?: null,
            hasNotStartedActivity: (bool) $xml->xpath('fizC_NiePodjetoDzialalnosci')[0] ?: null,
            cache: null,
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
            'registrationDeletionDate'  => $this->registrationDeletionDate,
            'registrationAuthorityCode' => $this->registrationAuthorityCode,
            'registryTypeCode'          => $this->registryTypeCode,
            'registrationAuthorityName' => $this->registrationAuthorityName,
            'registryTypeName'          => $this->registryTypeName,
            'legalFormCode'             => $this->legalFormCode,
            'detailedLegalFormCode'     => $this->detailedLegalFormCode,
            'financingFormCode'         => $this->financingFormCode,
            'ownershipFormCode'         => $this->ownershipFormCode,
            'foundingBodyCode'          => $this->foundingBodyCode,
            'legalFormName'             => $this->legalFormName,
            'detailedLegalFormName'     => $this->detailedLegalFormName,
            'financingFormName'         => $this->financingFormName,
            'ownershipFormName'         => $this->ownershipFormName,
            'foundingBodyName'          => $this->foundingBodyName,
            'localUnitsCount'           => $this->localUnitsCount,
            'hasNotStartedActivity'     => $this->hasNotStartedActivity,
            'cache'                     => $this->cache,
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
            $this->postalCode,
            $this->streetName,
            $this->buildingNumber,
            $this->apartmentNumber,
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
            bankAccount: null, // REGON doesn't provide bank account information
            cache: $this->cache,
        );
    }
}
