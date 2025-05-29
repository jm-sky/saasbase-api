<?php

namespace App\Services\ViesLookup\Integrations\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasXmlBody;

abstract class BaseViesRequest extends Request implements HasBody
{
    use HasXmlBody;

    protected Method $method = Method::POST;

    public function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction'   => '',
        ];
    }

    public function resolveEndpoint(): string
    {
        return '';
    }
}
