<?php

namespace App\Services\GusLookup\Integrations\Requests;

use Carbon\Carbon;
use Saloon\Enums\Method;

class SearchByRegonRequest extends BaseGusRequest
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $regon,
    ) {
        parent::__construct();
    }

    public function resolveEndpoint(): string
    {
        $date = Carbon::now()->format('Y-m-d');

        return "/api/search/regon/{$this->regon}?date={$date}";
    }
}
