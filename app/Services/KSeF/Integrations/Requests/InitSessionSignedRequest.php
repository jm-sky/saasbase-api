<?php

declare(strict_types=1);

namespace App\Services\KSeF\Integrations\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasStringBody;

class InitSessionSignedRequest extends Request implements HasBody
{
    use HasStringBody;

    public function __construct(
        protected string $signedDocument
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/online/Session/InitSigned';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/octet-stream',
            'Accept'       => 'application/json',
        ];
    }

    protected function defaultBody(): string
    {
        return base64_decode($this->signedDocument);
    }
}
