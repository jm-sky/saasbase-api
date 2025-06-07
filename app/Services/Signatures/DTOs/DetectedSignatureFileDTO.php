<?php

namespace App\Services\Signatures\DTOs;

use App\Services\Signatures\Enums\SignatureFileType;
use App\Services\Signatures\Enums\SignatureType;

readonly class DetectedSignatureFileDTO
{
    public function __construct(
        public SignatureFileType $type,
        public SignatureType $signature,
        public ?string $mime = null,
        public ?string $extension = null,
    ) {
    }
}
