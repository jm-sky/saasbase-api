<?php

declare(strict_types=1);

namespace App\Services\GusLookup\Integrations\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasXmlBody;

abstract class BaseGusRequest extends Request implements HasBody
{
    use HasXmlBody;

    protected Method $method = Method::POST;

    protected string $baseUrl = '';

    public function __construct()
    {
        $this->baseUrl = config('gus_lookup.api_url', 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc');
    }

    public function resolveEndpoint(): string
    {
        return '';
    }
}
