<?php

declare(strict_types=1);

namespace App\Services\KSeF\Integrations\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class TerminateSessionRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/online/Session/Terminate';
    }
}
