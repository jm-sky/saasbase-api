<?php

namespace App\Services\RegonLookup\Integrations\Responses;

use Saloon\Http\Response;

class RegonResponse extends Response
{
    public function xml(mixed ...$arguments): \SimpleXMLElement
    {
        preg_match('/<s:Envelope.*<\/s:Envelope>/s', $this->body(), $match);

        return simplexml_load_string($match[0])->xpath('//s:Body')[0];
    }
}
