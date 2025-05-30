<?php

namespace App\Services\RegonLookup\Integrations\Responses;

use App\Services\RegonLookup\Exceptions\RegonLookupException;
use Saloon\Http\Response;

class RegonResponse extends Response
{
    public function xml(mixed ...$arguments): \SimpleXMLElement
    {
        preg_match('/<s:Envelope.*<\/s:Envelope>/s', $this->body(), $match);

        if (empty($match)) {
            throw new RegonLookupException('Invalid response body');
        }

        $body = simplexml_load_string($match[0])->xpath('//s:Body');

        if (empty($body)) {
            throw new RegonLookupException('Invalid response XML body');
        }

        return $body[0];
    }
}
