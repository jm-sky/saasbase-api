<?php

namespace App\Services\IbanApi\Integrations\Requests;

use App\Services\IbanApi\DTOs\IbanApiResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ValidateIbanRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $iban,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/validate/{$this->iban}";
    }

    public function createDtoFromResponse(Response $response): IbanApiResponse
    {
        $data = $response->json();

        if (HttpFoundationResponse::HTTP_OK !== $data['result']) {
            throw new \Exception('Invalid IBAN');
        }

        return IbanApiResponse::fromArray($data);
    }
}
