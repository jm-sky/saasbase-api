<?php

declare(strict_types=1);

namespace App\Services\KSeF\Integrations\Requests;

use App\Services\KSeF\DTOs\SendInvoiceRequestDTO;
use Saloon\Enums\Method;

class SendInvoiceRequest extends BaseKSeFRequest
{
    protected Method $method = Method::PUT;

    public function __construct(
        protected SendInvoiceRequestDTO $invoiceData
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/online/Invoice/Send';
    }

    protected function defaultBody(): array
    {
        return [
            'invoiceHash' => [
                'hashSHA' => [
                    'algorithm' => $this->invoiceData->invoiceHash->hashSHA->algorithm,
                    'encoding'  => $this->invoiceData->invoiceHash->hashSHA->encoding,
                    'value'     => $this->invoiceData->invoiceHash->hashSHA->value,
                ],
                'fileSize' => $this->invoiceData->invoiceHash->fileSize,
            ],
            'invoicePayload' => [
                'type'                     => $this->invoiceData->invoicePayload->type,
                'invoiceBody'              => $this->invoiceData->invoicePayload->invoiceBody,
                'encryptedInvoiceBody'     => $this->invoiceData->invoicePayload->encryptedInvoiceBody,
                'encryptedInvoiceHash'     => $this->invoiceData->invoicePayload->encryptedInvoiceHash ? [
                    'hashSHA' => [
                        'algorithm' => $this->invoiceData->invoicePayload->encryptedInvoiceHash->hashSHA->algorithm,
                        'encoding'  => $this->invoiceData->invoicePayload->encryptedInvoiceHash->hashSHA->encoding,
                        'value'     => $this->invoiceData->invoicePayload->encryptedInvoiceHash->hashSHA->value,
                    ],
                    'fileSize' => $this->invoiceData->invoicePayload->encryptedInvoiceHash->fileSize,
                ] : null,
            ],
        ];
    }
}
