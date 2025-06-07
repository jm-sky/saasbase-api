<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class XmlSignatureVerifierService
{
    public const SIGNATURE_OK = 1;
    public const SIGNATURE_INVALID = 0;
    public const SIGNATURE_ERROR = -1;

    protected string $caBundlePath;

    public function __construct(?string $caBundlePath = null)
    {
        $this->caBundlePath = $caBundlePath ?? storage_path('app/certs/ca-bundle.pem');
    }

    public function verify(string $xmlPath): array
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        if (!$dom->load($xmlPath)) {
            return [
                'valid'      => false,
                'error'      => 'Nie udało się wczytać pliku XML.',
                'trusted_ca' => false,
                'details'    => null,
            ];
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        $signatureNode = $xpath->query('//ds:Signature')->item(0);

        if (!$signatureNode) {
            return [
                'valid'      => false,
                'error'      => 'Nie znaleziono węzła <Signature>.',
                'trusted_ca' => false,
                'details'    => null,
            ];
        }

        try {
            $objDSig = new XMLSecurityDSig();
            $objDSig->locateSignature($dom);
            $objDSig->canonicalizeSignedInfo();
            $objDSig->idKeys = ['ID'];

            $objKey = $objDSig->locateKey();
            $objDSig->readSignedInfo();

            $objKey->loadKey($objDSig->getX509Certificate(), false, true);
            $isValid = $objDSig->verify($objKey);

            $certPem = $objDSig->getX509Certificate();
            $certParsed = openssl_x509_parse($certPem);

            $isTrusted = $this->isCertificateTrusted($certPem);

            return [
                'valid'      => $isValid === 1,
                'trusted_ca' => $isTrusted,
                'details'    => [
                    'subject'       => $certParsed['subject'] ?? [],
                    'issuer'        => $certParsed['issuer'] ?? [],
                    'valid_from'    => isset($certParsed['validFrom_time_t']) ? date('c', $certParsed['validFrom_time_t']) : null,
                    'valid_to'      => isset($certParsed['validTo_time_t']) ? date('c', $certParsed['validTo_time_t']) : null,
                    'serial_number' => $certParsed['serialNumberHex'] ?? null,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'valid'      => false,
                'error'      => 'Błąd podczas weryfikacji: ' . $e->getMessage(),
                'trusted_ca' => false,
                'details'    => null,
            ];
        }
    }

    /**
     * Sprawdza, czy certyfikat pochodzi od zaufanego CA.
     */
    protected function isCertificateTrusted(string $certPem): bool
    {
        $verify = openssl_x509_checkpurpose($certPem, X509_PURPOSE_ANY, [$this->caBundlePath]);

        return $verify === true;
    }
}
