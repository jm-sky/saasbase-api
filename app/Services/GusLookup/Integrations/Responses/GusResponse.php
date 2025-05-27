<?php

namespace App\Services\GusLookup\Integrations\Responses;

use Saloon\Http\Response;

class GusResponse extends Response
{
    public function xml(mixed ...$arguments): \SimpleXMLElement
    {
        preg_match('/<s:Envelope.*<\\/s:Envelope>/s', $this->body(), $match);

        return simplexml_load_string($match[0])->xpath('//s:Body')[0];
    }
}
