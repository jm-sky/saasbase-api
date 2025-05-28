<?php

namespace App\Services\ViesLookup\Integrations\Requests;

use App\Services\ViesLookup\DTOs\ViesCheckResultDTO;
use Saloon\Http\Response;

class CheckVatRequest extends BaseViesRequest
{
    public function __construct(
        protected string $countryCode,
        protected string $vatNumber,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return ''; // Base URL is enough for SOAP service
    }

    protected function defaultBody(): string
    {
        $body = <<<XML
<tns:checkVat>
  <tns:countryCode>{$this->countryCode}</tns:countryCode>
  <tns:vatNumber>{$this->vatNumber}</tns:vatNumber>
</tns:checkVat>
XML;

        return $this->getSoapEnvelope($body);
    }

    public function createDtoFromResponse(Response $response): ViesCheckResultDTO
    {
        $response = $this->handleResponse($response);
        $data     = json_decode(json_encode(simplexml_load_string($response->body())), true);

        return ViesCheckResultDTO::fromApiResponse([
            'valid'       => $data['soap:Body']['checkVatResponse']['valid'] ?? false,
            'countryCode' => $data['soap:Body']['checkVatResponse']['countryCode'] ?? '',
            'vatNumber'   => $data['soap:Body']['checkVatResponse']['vatNumber'] ?? '',
            'requestDate' => $data['soap:Body']['checkVatResponse']['requestDate'] ?? '',
            'name'        => $data['soap:Body']['checkVatResponse']['name'] ?? null,
            'address'     => $data['soap:Body']['checkVatResponse']['address'] ?? null,
        ]);
    }
}
