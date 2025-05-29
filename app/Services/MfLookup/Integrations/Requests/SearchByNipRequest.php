<?php

namespace App\Services\MfLookup\Integrations\Requests;

use Carbon\Carbon;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchByNipRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $nip,
    ) {
    }

    public function resolveEndpoint(): string
    {
        $date = Carbon::now()->format('Y-m-d');

        return "/api/search/nip/{$this->nip}?date={$date}";
    }
}
