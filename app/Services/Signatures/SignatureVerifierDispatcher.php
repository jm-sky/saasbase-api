<?php

namespace App\Services\Signatures;

use App\Services\Signatures\DTOs\GenericSignaturesVerificationResultDTO;
use App\Services\Signatures\Enums\SignatureType;
use App\Services\Signatures\Exceptions\UnsupportedSignatureTypeException;

class SignatureVerifierDispatcher
{
    public function __construct(
        protected XmlSignatureVerifierService $xmlVerifier,
        protected PdfSignatureVerifierService $pdfVerifier,
    ) {
    }

    public function verify(string $content, SignatureType $type): GenericSignaturesVerificationResultDTO
    {
        return match ($type) {
            SignatureType::XAdES => $this->verifyXml($content),
            SignatureType::PAdES => $this->verifyPdfContent($content),
            default              => throw new UnsupportedSignatureTypeException("Unsupported signature type: {$type->value}"),
        };
    }

    protected function verifyXml(string $xml): GenericSignaturesVerificationResultDTO
    {
        $xmlResult = $this->xmlVerifier->verify($xml);

        return new GenericSignaturesVerificationResultDTO(
            valid: $xmlResult->valid,
            type: SignatureType::XAdES,
            signatures: $xmlResult->signatures
        );
    }

    protected function verifyPdfContent(string $content): GenericSignaturesVerificationResultDTO
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($tempPath, $content);

        try {
            $pdfResult = $this->pdfVerifier->verify($tempPath);

            return new GenericSignaturesVerificationResultDTO(
                valid: $pdfResult->valid,
                type: SignatureType::PAdES,
                signatures: $pdfResult->signatures,
            );
        } finally {
            unlink($tempPath);
        }
    }
}
