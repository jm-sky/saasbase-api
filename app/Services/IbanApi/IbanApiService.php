<?php

namespace App\Services\IbanApi;

use App\Services\IbanApi\DTOs\IbanApiResponse;
use App\Services\IbanApi\Integrations\IbanApiConnector;
use App\Services\IbanApi\Integrations\Requests\ValidateIbanRequest;

class IbanApiService
{
    public function getIbanInfo(string $iban, bool $throw = false): ?IbanApiResponse
    {
        $connector = new IbanApiConnector();
        $response  = $connector->send(new ValidateIbanRequest($iban));

        if ($throw) {
            return $response->dtoOrFail();
        }

        if ($response->failed()) {
            return null;
        }

        return $response->dto();
    }

    public function getSwiftForIban(string $iban, bool $throw = false): ?string
    {
        $info = $this->getIbanInfo($iban, $throw);

        return $info->data->bank->bic ?? null;
    }
}
