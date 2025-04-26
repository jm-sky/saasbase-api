<?php

namespace App\Domain\CompanyLookup\Integrations\Requests;

use Saloon\Http\Request;
use Saloon\Enums\Method;
use Carbon\Carbon;

class SearchByNipRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $nip,
    ) {}

    public function resolveEndpoint(): string
    {
        $date = Carbon::now()->format('Y-m-d');

        return "/api/search/nip/{$this->nip}?date={$date}";
    }
}
