<?php

namespace App\Domain\Utils\Resources;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\DTOs\BankAccountDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string                      $name
 * @property string                      $country
 * @property ?string                     $vatId
 * @property ?string                     $regon
 * @property ?string                     $shortName
 * @property ?string                     $phoneNumber
 * @property ?string                     $email
 * @property ?string                     $website
 * @property ?AddressDTO                 $address
 * @property ?BankAccountDTO             $bankAccount
 * @property ?CommonCompanyLookupSources $sources
 */
class CommonCompanyLookupResource extends JsonResource
{
    public function __construct(CommonCompanyLookupData $resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        /** @var CommonCompanyLookupData $data */
        $data = $this->resource;

        return [
            'country'     => $data->country,
            'name'        => $data->name,
            'vatId'       => $data->vatId,
            'regon'       => $data->regon,
            'shortName'   => $data->shortName,
            'phoneNumber' => $data->phoneNumber,
            'email'       => $data->email,
            'website'     => $data->website,
            'address'     => $data->address?->toArray(),
            'bankAccount' => $data->bankAccount?->toArray(),
            'sources'     => $data->sources?->toArray(),
        ];
    }
}
