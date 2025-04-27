<?php

namespace App\Domain\ViesLookup\Integrations\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class CheckVatRequest extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        protected string $countryCode,
        protected string $vatNumber,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return ''; // Base URL is enough for SOAP service
    }

    public function defaultBody(): array
    {
        return [
            'countryCode' => $this->countryCode,
            'vatNumber'   => $this->vatNumber,
        ];
    }

    public function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction'   => '',
        ];
    }

    protected function defaultBodyAsString(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tns="urn:ec.europa.eu:taxud:vies:services:checkVat:types">
  <soap:Body>
    <tns:checkVat>
      <tns:countryCode>{$this->countryCode}</tns:countryCode>
      <tns:vatNumber>{$this->vatNumber}</tns:vatNumber>
    </tns:checkVat>
  </soap:Body>
</soap:Envelope>
XML;
    }
}
