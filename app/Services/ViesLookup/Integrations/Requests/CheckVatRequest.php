<?php

namespace App\Services\ViesLookup\Integrations\Requests;

use App\Services\ViesLookup\DTOs\ViesLookupResultDTO;
use App\Services\ViesLookup\Exceptions\ViesLookupException;
use Saloon\Http\Response;

class CheckVatRequest extends BaseViesRequest
{
    public function __construct(
        protected string $countryCode,
        protected string $vatNumber,
    ) {
    }

    protected function defaultBody(): ?string
    {
        return <<<XML
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:ec.europa.eu:taxud:vies:services:checkVat:types">
               <soapenv:Body>
                  <urn:checkVat>
                     <urn:countryCode>{$this->countryCode}</urn:countryCode>
                     <urn:vatNumber>{$this->vatNumber}</urn:vatNumber>
                  </urn:checkVat>
               </soapenv:Body>
            </soapenv:Envelope>
        XML;
    }

    public function createDtoFromResponse(Response $response): ViesLookupResultDTO
    {
        $xml = $response->xml();

        if (false === $xml) {
            throw new ViesLookupException('Invalid VIES XML response.');
        }

        $isValid     = (string) ($xml->xpath('//urn:valid')[0] ?? 'false');
        $faultString = (string) ($xml->xpath('//env:Fault/faultstring')[0] ?? 'Unknown error');

        if ('true' !== $isValid) {
            throw new ViesLookupException('VIES API error: ' . $faultString);
        }

        return ViesLookupResultDTO::fromXml($xml);
    }
}
