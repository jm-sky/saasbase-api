<?php

declare(strict_types=1);

namespace App\Services\KSeF\Integrations\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class InvoiceStatusRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $invoiceElementReferenceNumber
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/online/Invoice/Status/{$this->invoiceElementReferenceNumber}";
    }
}
