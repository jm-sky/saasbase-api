<?php

namespace App\Services;

class PdfSignatureVerifierService
{
    public const SIGNATURE_OK = 1;

    public const SIGNATURE_INVALID = 0;

    public const SIGNATURE_ERROR = -1;

    protected string $caBundlePath;

    public function __construct(?string $caBundlePath = null)
    {
        $this->caBundlePath = $caBundlePath ?? storage_path('app/certs/ca-bundle.pem');
    }

    /**
     * Weryfikuje podpis CMS w pliku PDF.
     */
    public function verifySignature(string $pdfPath): array
    {
        // Wyodrębnij podpis z PDF (tu przyjmujemy, że podpisany plik to CMS/p7m/PAdES)
        $tempCms = tempnam(sys_get_temp_dir(), 'cms_');
        $certOut = tempnam(sys_get_temp_dir(), 'cert_');

        // Extract CMS from signed PDF - for MVP, assume already extracted
        // Można to rozszerzyć o extractor np. na bazie pdftk / qpdf

        // Tymczasowo zakładamy, że PDF jest po prostu podpisanym plikiem CMS
        $result = openssl_pkcs7_verify($pdfPath, 0, $certOut, [], $this->caBundlePath, $outFile = null);

        if (false === $result) {
            if (file_exists($certOut)) {
                unlink($certOut);
            }

            return [
                'valid'      => false,
                'error'      => 'OpenSSL error or invalid CMS signature.',
                'trusted_ca' => false,
                'details'    => null,
            ];
        }

        $certContent = file_get_contents($certOut);

        if (file_exists($certOut)) {
            unlink($certOut);
        }

        $certParsed = openssl_x509_parse($certContent);

        $isTrusted = $this->isCertificateTrusted($certContent);

        return [
            'valid'      => self::SIGNATURE_OK === $result,
            'trusted_ca' => $isTrusted,
            'details'    => [
                'subject'       => $certParsed['subject'] ?? [],
                'issuer'        => $certParsed['issuer'] ?? [],
                'valid_from'    => isset($certParsed['validFrom_time_t']) ? date('c', $certParsed['validFrom_time_t']) : null,
                'valid_to'      => isset($certParsed['validTo_time_t']) ? date('c', $certParsed['validTo_time_t']) : null,
                'serial_number' => $certParsed['serialNumberHex'] ?? null,
            ],
        ];
    }

    /**
     * Sprawdza czy certyfikat pochodzi od zaufanego CA.
     */
    protected function isCertificateTrusted(string $certPem): bool
    {
        $verify = openssl_x509_checkpurpose($certPem, X509_PURPOSE_ANY, [$this->caBundlePath]);

        return true === $verify;
    }
}
