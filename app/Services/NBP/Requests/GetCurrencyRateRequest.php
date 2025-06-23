<?php

namespace App\Services\NBP\Requests;

use App\Services\NBP\Enums\NBPTableEnum;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCurrencyRateRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected NBPTableEnum $table,
        protected string $code,
        protected ?string $date = null
    ) {}

    public function resolveEndpoint(): string
    {
        $endpoint = "/exchangerates/rates/{$this->table->value}/{$this->code}";
        
        if ($this->date) {
            $endpoint .= "/{$this->date}";
        }
        
        return $endpoint;
    }
}
