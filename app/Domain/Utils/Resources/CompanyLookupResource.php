<?php

namespace App\Domain\Utils\Resources;

use App\Domain\Utils\DTOs\CompanyPersonDTO;
use App\Services\CompanyLookup\DTOs\CompanyLookupResultDTO;
use App\Services\ViesLookup\DTOs\ViesLookupResultDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CompanyLookupResultDTO|ViesLookupResultDTO $resource
 * @property string                                     $name                  Company name
 * @property string                                     $vatId                 VAT identification number (NIP for PL, VAT number for others)
 * @property string                                     $country               Two-letter country code
 * @property ?string                                    $regon                 REGON number (PL only)
 * @property ?string                                    $krs                   KRS number (PL only)
 * @property ?string                                    $address               Company address
 * @property ?string                                    $workingAddress        Working address (PL only)
 * @property string[]                                   $accountNumbers        Bank account numbers (PL only)
 * @property string                                     $vatStatus             VAT status ('Czynny'|'Zwolniony'|'Nieczynny' for PL, 'active'|'inactive' for others)
 * @property bool                                       $hasVirtualAccounts    Whether company has virtual accounts (PL only)
 * @property CompanyPersonDTO[]                         $representatives       Company representatives (PL only)
 * @property CompanyPersonDTO[]                         $authorizedClerks      Authorized clerks (PL only)
 * @property CompanyPersonDTO[]                         $partners              Company partners (PL only)
 * @property ?string                                    $registrationLegalDate Company registration date (PL only)
 * @property string                                     $source                Source of the data ('mf' for MF, 'vies' for VIES)
 * @property mixed                                      $data                  Raw data from the source
 */
class CompanyLookupResource extends JsonResource
{
    public function __construct(CompanyLookupResultDTO|ViesLookupResultDTO $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        if ($this->resource instanceof CompanyLookupResultDTO) {
            return $this->formatPolishCompany();
        }

        return $this->formatForeignCompany();
    }

    protected function formatPolishCompany(): array
    {
        /** @var CompanyLookupResultDTO $company */
        $company = $this->resource;

        return [
            'name'                  => $company->name,
            'vatId'                 => $company->nip,
            'country'               => 'PL',
            'regon'                 => $company->regon,
            'krs'                   => $company->krs,
            'address'               => $company->residenceAddress,
            'vatStatus'             => $company->vatStatus->value,
            'workingAddress'        => $company->workingAddress,
            'accountNumbers'        => $company->accountNumbers,
            'hasVirtualAccounts'    => $company->hasVirtualAccounts,
            'representatives'       => CompanyPersonDTO::collect($company->representatives),
            'authorizedClerks'      => CompanyPersonDTO::collect($company->authorizedClerks),
            'partners'              => CompanyPersonDTO::collect($company->partners),
            'registrationLegalDate' => $company->registrationLegalDate,
            'source'                => 'mf',
            'data'                  => $company->toArray(),
        ];
    }

    protected function formatForeignCompany(): array
    {
        /** @var ViesLookupResultDTO $company */
        $company = $this->resource;

        return [
            'name'      => $company->name,
            'vatId'     => $company->vatNumber,
            'country'   => $company->countryCode,
            'address'   => $company->address,
            'vatStatus' => $company->valid ? 'active' : 'inactive',
            'source'    => 'vies',
            'data'      => $company,
        ];
    }
}
