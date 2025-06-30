<?php

namespace App\Services\Signatures\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Services\Signatures\Enums\SignatureType;

/**
 * @property bool                              $valid
 * @property SignatureType                     $type
 * @property array<GenericSignatureDetailsDTO> $signatures
 * @property ?string                           $error
 */
final class GenericSignaturesVerificationResultDTO extends BaseDataDTO
{
    public function __construct(
        public bool $valid,
        public SignatureType $type,
        public array $signatures,
        public ?string $error = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'valid'      => $this->valid,
            'type'       => $this->type->value,
            'signatures' => collect($this->signatures)->map(fn (GenericSignatureDetailsDTO $signature) => $signature->toArray())->toArray(),
            'error'      => $this->error,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['valid'] ?? false,
            $data['type'] ?? SignatureType::XAdES,
            $data['signatures'] ?? [],
            $data['error'] ?? null,
        );
    }
}
