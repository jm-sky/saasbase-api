<?php

namespace App\Services\NBP\Requests;

use App\Services\NBP\Enums\NBPTableEnum;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetExchangeRatesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected NBPTableEnum $table = NBPTableEnum::A,
        protected ?string $date = null
    ) {
    }

    public function resolveEndpoint(): string
    {
        $endpoint = "/exchangerates/tables/{$this->table->value}";

        if ($this->date) {
            $endpoint .= "/{$this->date}";
        }

        return $endpoint;
    }
}
