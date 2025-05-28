<?php

namespace App\Services\ViesLookup\Integrations\Requests;

use App\Services\ViesLookup\Exceptions\ViesLookupException;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

abstract class BaseViesRequest extends Request
{
    protected Method $method = Method::POST;

    public function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction'   => '',
        ];
    }

    protected function handleResponse(Response $response): Response
    {
        if (!$response->successful()) {
            throw new ViesLookupException('Unsuccessful VIES API response: ' . $response->status());
        }

        $xml = simplexml_load_string($response->body());

        if (false === $xml) {
            throw new ViesLookupException('Invalid VIES XML response.');
        }

        // Convert SOAP response to array
        $data = json_decode(json_encode($xml), true);

        // Check for SOAP fault
        if (isset($data['soap:Body']['soap:Fault'])) {
            $fault = $data['soap:Body']['soap:Fault'];

            throw new ViesLookupException('VIES API error: ' . ($fault['faultstring'] ?? 'Unknown error'));
        }

        return $response;
    }

    protected function getSoapEnvelope(string $body): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tns="urn:ec.europa.eu:taxud:vies:services:checkVat:types">
  <soap:Body>
    {$body}
  </soap:Body>
</soap:Envelope>
XML;
    }
}
