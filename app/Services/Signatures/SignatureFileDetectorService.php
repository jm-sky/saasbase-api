<?php

namespace App\Services\Signatures;

use App\Services\Signatures\DTOs\DetectedSignatureFileDTO;
use App\Services\Signatures\Enums\SignatureFileType;
use App\Services\Signatures\Enums\SignatureType;

class SignatureFileDetectorService
{
    public function detect(string $rawContent, ?string $filename = null): DetectedSignatureFileDTO
    {
        $extension = $filename ? strtolower(pathinfo($filename, PATHINFO_EXTENSION)) : null;

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->buffer($rawContent);

        $type      = SignatureFileType::UNKNOWN;
        $signature = SignatureType::UNKNOWN;

        if (str_starts_with(trim($rawContent), '<?xml')) {
            if (str_contains($rawContent, '<ds:Signature') || str_contains($rawContent, '<Signature')) {
                $type      = SignatureFileType::XML;
                $signature = SignatureType::XAdES;
            }
        } elseif (str_starts_with($rawContent, '%PDF')) {
            $type      = SignatureFileType::PDF;
            $signature = SignatureType::PAdES;
        } elseif ('PK' === substr($rawContent, 0, 2)) {
            $type      = SignatureFileType::ZIP;
            $signature = SignatureType::ASIC_E;
        } elseif (
            str_contains($mime, 'pkcs7')
            || in_array($extension, ['p7m', 'p7s', 'p7c'])
        ) {
            $type      = SignatureFileType::BINARY;
            $signature = SignatureType::CAdES;
        }

        return new DetectedSignatureFileDTO(
            type: $type,
            signature: $signature,
            mime: $mime,
            extension: $extension,
        );
    }
}
