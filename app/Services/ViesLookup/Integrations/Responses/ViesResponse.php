<?php

namespace App\Services\ViesLookup\Integrations\Responses;

use Saloon\Http\Response;

class ViesResponse extends Response
{
    public function xml(mixed ...$arguments): \SimpleXMLElement
    {
        $xml = simplexml_load_string($this->body());
        $xml->registerXPathNamespace('env', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('ns2', 'urn:ec.europa.eu:taxud:vies:services:checkVat:types');
        $xml->registerXPathNamespace('urn', 'urn:ec.europa.eu:taxud:vies:services:checkVat:types');

        return $xml;
    }
}
