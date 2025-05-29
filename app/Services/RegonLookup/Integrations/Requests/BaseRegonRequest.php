<?php

declare(strict_types=1);

namespace App\Services\RegonLookup\Integrations\Requests;

use App\Services\RegonLookup\Exceptions\RegonLookupException;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasXmlBody;

abstract class BaseRegonRequest extends Request implements HasBody
{
    use HasXmlBody;

    protected string $baseUrl = '';

    protected Method $method = Method::POST;

    public function __construct()
    {
        $this->baseUrl = config('regon_lookup.api_url', 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc');
    }

    public function resolveEndpoint(): string
    {
        return '';
    }

    protected function defaultException(): string
    {
        return RegonLookupException::class;
    }
}
