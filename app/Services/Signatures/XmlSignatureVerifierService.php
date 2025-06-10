<?php

namespace App\Services\Signatures;

use App\Services\Signatures\DTOs\CertificateDTO;
use App\Services\Signatures\DTOs\GenericSignatureDetailsDTO;
use App\Services\Signatures\DTOs\GenericSignaturesVerificationResultDTO;
use App\Services\Signatures\DTOs\SignerIdentityDTO;
use App\Services\Signatures\Enums\SignatureType;

class XmlSignatureVerifierService
{
    protected string $caBundlePath;

    public function __construct(?string $caBundlePath = null)
    {
        $this->caBundlePath = $caBundlePath ?? storage_path('app/certs/ca-bundle.pem');
    }

    public function verify(string $xmlContent): GenericSignaturesVerificationResultDTO
    {
        $dom                     = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xmlContent, LIBXML_NONET | LIBXML_NOBLANKS);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        $xpath->registerNamespace('ppZP', 'http://crd.gov.pl/xml/schematy/ppzp/');
        $xpath->registerNamespace('os', 'http://crd.gov.pl/xml/schematy/osoba/2009/03/06/');

        $signatureNodes = $xpath->query("//*[local-name()='Signature' and namespace-uri()='http://www.w3.org/2000/09/xmldsig#']");
        $signatures     = [];
        $allValid       = true;

        foreach ($signatureNodes as $signatureNode) {
            $certificateNode = $xpath->query('.//ds:X509Certificate', $signatureNode)->item(0);
            $certBase64      = $certificateNode?->nodeValue;
            $certPem         = $this->wrapCertificate($certBase64);

            $isTrusted   = $this->isCertificateTrusted($certPem);
            $personData  = $this->extractPersonData($xpath, $signatureNode);
            $certDetails = $this->parseCertificateDetails($certPem);

            $signatures[] = new GenericSignatureDetailsDTO(
                valid: $isTrusted,
                trustedCA: $isTrusted,
                signerIdentity: new SignerIdentityDTO(
                    firstName: $personData['first_name'] ?? null,
                    lastName: $personData['last_name'] ?? null,
                    pesel: $personData['pesel'] ?? null,
                ),
                certificate: new CertificateDTO(
                    issuer: $certDetails['issuer'] ?? null,
                    serialNumber: $certDetails['serial'] ?? null,
                    subject: $certDetails['subject'] ?? null,
                    validFrom: $certDetails['valid_from'] ?? null,
                    validTo: $certDetails['valid_to'] ?? null,
                ),
            );

            if (!$isTrusted) {
                $allValid = false;
            }
        }

        return new GenericSignaturesVerificationResultDTO(
            valid: $allValid,
            type: SignatureType::XAdES,
            signatures: $signatures
        );
    }

    protected function wrapCertificate(?string $base64): ?string
    {
        if (!$base64) {
            return null;
        }

        return "-----BEGIN CERTIFICATE-----\n" .
            chunk_split(trim($base64), 64, "\n") .
            "-----END CERTIFICATE-----\n";
    }

    protected function isCertificateTrusted(?string $certPem): bool
    {
        if (!$certPem) {
            return false;
        }

        return true === openssl_x509_checkpurpose($certPem, X509_PURPOSE_ANY, [$this->caBundlePath]);
    }

    protected function parseCertificateDetails(?string $certPem): array
    {
        if (!$certPem || !$parsed = openssl_x509_parse($certPem)) {
            return [];
        }

        return [
            'issuer'     => $parsed['issuer']['CN'] ?? null,
            'serial'     => $parsed['serialNumberHex'] ?? null,
            'subject'    => $parsed['subject']['CN'] ?? null,
            'valid_from' => isset($parsed['validFrom_time_t']) ? date('c', $parsed['validFrom_time_t']) : null,
            'valid_to'   => isset($parsed['validTo_time_t']) ? date('c', $parsed['validTo_time_t']) : null,
        ];
    }

    protected function extractPersonData(\DOMXPath $xpath, \DOMNode $signatureNode): array
    {
        $data = [];

        $firstNameNode = $xpath->query('.//ppZP:DaneZPOsobyFizycznej/os:Imie', $signatureNode)?->item(0);
        $lastNameNode  = $xpath->query('.//ppZP:DaneZPOsobyFizycznej/os:Nazwisko', $signatureNode)?->item(0);
        $peselNode     = $xpath->query('.//ppZP:DaneZPOsobyFizycznej/os:PESEL', $signatureNode)?->item(0);

        if ($firstNameNode) {
            $data['first_name'] = $firstNameNode->nodeValue;
        }

        if ($lastNameNode) {
            $data['last_name'] = $lastNameNode->nodeValue;
        }

        if ($peselNode) {
            $data['pesel'] = $peselNode->nodeValue;
        }

        return $data;
    }
}
