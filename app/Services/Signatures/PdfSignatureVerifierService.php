<?php

namespace App\Services\Signatures;

use App\Services\Signatures\DTOs\CertificateDTO;
use App\Services\Signatures\DTOs\GenericSignatureDetailsDTO;
use App\Services\Signatures\DTOs\GenericSignaturesVerificationResultDTO;
use App\Services\Signatures\Enums\SignatureType;

class PdfSignatureVerifierService
{
    protected string $caBundlePath;

    public function __construct(?string $caBundlePath = null)
    {
        $this->caBundlePath = $caBundlePath ?? storage_path('app/certs/ca-bundle.pem');
    }

    public function verify(string $pdfPath): GenericSignaturesVerificationResultDTO
    {
        try {
            // TODO: In the future, extract CMS from PDF (e.g., PAdES). For now, assume CMS.
            $certContent = $this->extractCertificate($pdfPath);

            if (!$certContent) {
                throw new \RuntimeException('No certificate extracted.');
            }

            $parsed    = $this->parseCertificateDetails($certContent);
            $isTrusted = $this->isCertificateTrusted($certContent);

            $signatureDTO = new GenericSignatureDetailsDTO(
                valid: true,
                trustedCA: $isTrusted,
                certificate: new CertificateDTO(
                    issuer: $parsed['issuer'] ?? null,
                    serialNumber: $parsed['serial'] ?? null,
                    subject: $parsed['subject'] ?? null,
                    validFrom: $parsed['valid_from'] ?? null,
                    validTo: $parsed['valid_to'] ?? null,
                ),
            );

            return new GenericSignaturesVerificationResultDTO(
                valid: $isTrusted,
                signatures: [$signatureDTO],
                type: SignatureType::PAdES
            );
        } catch (\Exception $e) {
            return new GenericSignaturesVerificationResultDTO(
                valid: false,
                signatures: [],
                type: SignatureType::PAdES,
                error: $e->getMessage()
            );
        }
    }

    protected function extractCertificate(string $pdfPath): ?string
    {
        $certOut = tempnam(sys_get_temp_dir(), 'cert_');

        $result = openssl_pkcs7_verify($pdfPath, 0, $certOut, [], $this->caBundlePath);

        if (false === $result) {
            if (file_exists($certOut)) {
                unlink($certOut);
            }

            throw new \RuntimeException('OpenSSL verification failed: ' . openssl_error_string());
        }

        $certContent = file_get_contents($certOut);
        unlink($certOut);

        return $certContent ?: null;
    }

    protected function isCertificateTrusted(string $certPem): bool
    {
        return true === openssl_x509_checkpurpose($certPem, X509_PURPOSE_ANY, [$this->caBundlePath]);
    }

    protected function parseCertificateDetails(string $certPem): array
    {
        $parsed = openssl_x509_parse($certPem);

        return [
            'issuer'     => $parsed['issuer']['CN'] ?? null,
            'serial'     => $parsed['serialNumberHex'] ?? null,
            'subject'    => $parsed['subject']['CN'] ?? null,
            'valid_from' => isset($parsed['validFrom_time_t']) ? date('c', $parsed['validFrom_time_t']) : null,
            'valid_to'   => isset($parsed['validTo_time_t']) ? date('c', $parsed['validTo_time_t']) : null,
        ];
    }
}
