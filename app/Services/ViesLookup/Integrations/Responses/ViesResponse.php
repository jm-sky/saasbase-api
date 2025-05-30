<?php

namespace App\Services\ViesLookup\Integrations\Responses;

use App\Services\ViesLookup\Exceptions\ViesLookupException;
use Saloon\Http\Response;

class ViesResponse extends Response
{
    public function xml(mixed ...$arguments): \SimpleXMLElement
    {
        try {
            $xml = simplexml_load_string($this->body());
            $xml->registerXPathNamespace('env', 'http://schemas.xmlsoap.org/soap/envelope/');
            $xml->registerXPathNamespace('ns2', 'urn:ec.europa.eu:taxud:vies:services:checkVat:types');
            $xml->registerXPathNamespace('urn', 'urn:ec.europa.eu:taxud:vies:services:checkVat:types');

            return $xml;
        } catch (\Exception $e) {
            throw new ViesLookupException('Invalid VIES XML response');
        }
    }
}
