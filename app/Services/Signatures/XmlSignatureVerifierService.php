<?php

namespace App\Services\Signatures;

use App\Services\Signatures\DTOs\XmlSignatureDetailsDTO;
use App\Services\Signatures\DTOs\XmlSignaturesVerificationResultDTO;
use DOMDocument;
use DOMXPath;

class XmlSignatureVerifierService
{
    protected string $caBundlePath;

    public function __construct(?string $caBundlePath = null)
    {
        $this->caBundlePath = $caBundlePath ?? storage_path('app/certs/ca-bundle.pem');
    }

    public function verify(string $xmlContent): XmlSignaturesVerificationResultDTO
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xmlContent, LIBXML_NONET | LIBXML_NOBLANKS);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        $xpath->registerNamespace('ppZP', 'http://crd.gov.pl/xml/schematy/ppzp/');
        $xpath->registerNamespace('os', 'http://crd.gov.pl/xml/schematy/osoba/2009/03/06/');

        $signatureNodes = $xpath->query("//*[local-name()='Signature' and namespace-uri()='http://www.w3.org/2000/09/xmldsig#']");
        $signatures = [];
        $valid = true;

        foreach ($signatureNodes as $signatureNode) {
            $certificateNode = $xpath->query(".//ds:X509Certificate", $signatureNode)->item(0);
            $certBase64 = $certificateNode?->nodeValue;

            $certPem = $this->wrapCertificate($certBase64);
            $isTrusted = $this->isCertificateTrusted($certPem);

            $personData = $this->extractPersonData($xpath, $signatureNode);

            $signatures[] = new XmlSignatureDetailsDTO(
                valid: $isTrusted,
                trustedCA: $isTrusted,
                personFirstName: $personData['first_name'] ?? null,
                personLastName: $personData['last_name'] ?? null,
                personPESEL: $personData['pesel'] ?? null,
                certificateIssuer: null, // Można dodać z x509_parse
                certificateSerial: null, // jw.
            );

            if (!$isTrusted) {
                $valid = false;
            }
        }

        return new XmlSignaturesVerificationResultDTO(
            valid: $valid,
            signatures: $signatures
        );
    }

    protected function wrapCertificate(?string $base64): string
    {
        if (!$base64) {
            return '';
        }

        return "-----BEGIN CERTIFICATE-----\n" .
            chunk_split($base64, 64, "\n") .
            "-----END CERTIFICATE-----\n";
    }

    protected function isCertificateTrusted(string $certPem): bool
    {
        if (!$certPem) {
            return false;
        }

        return openssl_x509_checkpurpose($certPem, X509_PURPOSE_ANY, [$this->caBundlePath]) === true;
    }

    protected function extractPersonData(DOMXPath $xpath, \DOMNode $signatureNode): array
    {
        $data = [];

        $firstNameNode = $xpath->query(".//ppZP:DaneZPOsobyFizycznej/os:Imie", $signatureNode)?->item(0);
        $lastNameNode = $xpath->query(".//ppZP:DaneZPOsobyFizycznej/os:Nazwisko", $signatureNode)?->item(0);
        $peselNode = $xpath->query(".//ppZP:DaneZPOsobyFizycznej/os:PESEL", $signatureNode)?->item(0);

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
