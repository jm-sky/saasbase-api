<?php

declare(strict_types=1);

namespace App\Services\KSeF\Integrations\Requests;

use App\Services\KSeF\Exceptions\KSeFException;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

abstract class BaseKSeFRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    protected function defaultException(): string
    {
        return KSeFException::class;
    }
}
