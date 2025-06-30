<?php

namespace App\Domain\IdentityCheck\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Services\Signatures\DTOs\GenericSignaturesVerificationResultDTO;

final class IdentityConfirmationResponseDTO extends BaseDataDTO
{
    public function __construct(
        public IdentityConfirmationResponseStatus $status,
        public bool $confirmed,
        public ?array $errors,
        public ?array $signatureInfo,
    ) {
    }

    public static function fromXmlValidationException(\RuntimeException $e): self
    {
        return new self(
            status: IdentityConfirmationResponseStatus::INVALID_XML,
            confirmed: false,
            errors: [$e->getMessage()],
            signatureInfo: null,
        );
    }

    public static function fromEmptySignatureVerificationResult(GenericSignaturesVerificationResultDTO $verifyResult): static
    {
        return new self(
            status: IdentityConfirmationResponseStatus::INVALID_SIGNATURE,
            confirmed: false,
            errors: $verifyResult->error ? [$verifyResult->error] : ['Invalid signature'],
            signatureInfo: $verifyResult->signatures[0]->toArray() ?? null,
        );
    }

    public static function fromConfirmedIdentityData(
        ConfirmedIdentityDataDTO $confirmedIdentityData,
        GenericSignaturesVerificationResultDTO $verifyResult,
        bool $signatureValid,
    ): static {
        $signatureInfo = $verifyResult->getSignatureBySignerName($confirmedIdentityData->fullName);

        return new self(
            status: $confirmedIdentityData->fullName ? IdentityConfirmationResponseStatus::VERIFIED : IdentityConfirmationResponseStatus::UNVERIFIED,
            confirmed: $confirmedIdentityData->fullName && $signatureValid,
            errors: null,
            signatureInfo: $signatureInfo ? $signatureInfo->toArray() : null,
        );
    }

    public function toArray(): array
    {
        return [
            'status'        => $this->status->value,
            'confirmed'     => $this->confirmed,
            'errors'        => $this->errors,
            'signatureInfo' => $this->signatureInfo,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            $data['status'] ?? IdentityConfirmationResponseStatus::UNVERIFIED,
            $data['confirmed'] ?? false,
            $data['errors'] ?? null,
            $data['signatureInfo'] ?? null,
        );
    }
}
