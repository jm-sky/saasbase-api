# Zadanie: MVP walidacji PAdES (PDF ePUAP) w czystym PHP 🚀

## Cel  
Za pomocą darmowych narzędzi w PHP zaimplementować wstępną walidację podpisów PAdES (kwalifikowany podpis ePUAP) i ekstrakcję danych podpisującego (imię, nazwisko, PESEL).

---

## 🔧 Narzędzia i podejścia

### 1. **Wywołanie OpenSSL przez `exec()`**
- Wyciąganie podpisu PKCS7 z PDF-a (np. narzędziem `pdfsig` lub własnym parserem).
- Zapis podpisu do pliku `.p7s`.
- Ekstrakcja certyfikatu:
  ```bash
  openssl pkcs7 -in sig.p7s -print_certs -out signer.pem
  ```
- Parsowanie certyfikatu w PHP:
  ```php
  $cert = openssl_x509_parse(file_get_contents('signer.pem'));
  $name = $cert['subject']['CN'] ?? null;
  // regexp na PESEL w subjectDN lub CN
  ```
- (Opcjonalnie) weryfikacja podpisu:
  ```bash
  openssl smime -verify -in sig.p7s -inform DER -content content.dat -CAfile ca.pem
  ```

### 2. **Walidacja podpisów XML (XAdES)**  
Korzystając z `xmlseclibs`:
- Parsowanie XML
- Weryfikacja podpisu
- Ekstrakcja certyfikatu i jego pól

---

## ⚠️ Ograniczenia MVP

- **Brak pełnej walidacji PAdES** — PHP nie oferuje wbudowanej obsługi formatu podpisu w PDF.
- **Brak automatycznego sprawdzania CRL/OCSP** — wymagałoby dodatkowej implementacji.
- **PDFsig lub podobne** narzędzia mogą nie być dostępne na serwerze.
- **Wywoływanie `exec()` niesie ryzyko bezpieczeństwa**, trzeba je dobrze zabezpieczyć (kontrola ścieżki, limit czasu, itp.).

---

## 📦 Open-source narzędzia powiązane
- Na czysto w PHP: brak pełnego wsparcia PAdES 0.
- Można użyć `Namirial PHP Appliance`: dodaje REST API do weryfikacji PAdES/CAdES/XAdES, darmowy tryb verify-only 1.

---

## ✅ Etapy MVP

1. Upload PDF doc w Laravel.
2. `exec()`:
   - wyodrębnienie `.p7s`
   - ekstrakcja certyfikatu do PEM
3. `openssl_x509_parse()` w PHP — odczyt CN/serialNumber i dat.
4. Parsowanie PESEL przez regex.
5. (Opcjonalnie) `openssl smime -verify` — sprawdzenie podpisu.
6. Zwrócenie JSON w API:
   ```json
   {
     "valid": true,
     "name": "...",
     "pesel": "...",
     "issuer": "...",
     "valid_from": "...",
     "valid_to": "..."
   }
   ```

---

## 📌 Podsumowanie

⁣To rozwiązanie MVP pozwoli na darmowe wykonanie podstawowej walidacji ePUAP/PAdES w PHP, bez zewnętrznych serwisów, choć z ograniczeniami funkcjonalnymi. Idealne na szybki start, przed późniejszym przejściem na pełny mikroserwis z EU DSS.

Możesz dołączyć aktualne CA z https://curl.se/docs/caextract.html jako ca-bundle.pem.


## Example implementation

```php
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
     * Weryfikuje podpis CMS w pliku PDF
     */
    public function verifySignature(string $pdfPath): array
    {
        // Wyodrębnij podpis z PDF (tu przyjmujemy, że podpisany plik to CMS/p7m/PAdES)
        $tempCms = tempnam(sys_get_temp_dir(), 'cms_');

        // Extract CMS from signed PDF - for MVP, assume already extracted
        // Można to rozszerzyć o extractor np. na bazie pdftk / qpdf

        // Tymczasowo zakładamy, że PDF jest po prostu podpisanym plikiem CMS
        $result = openssl_pkcs7_verify($pdfPath, 0, $certOut = null, [], $this->caBundlePath, $outFile = null);

        if ($result === false) {
            return [
                'valid' => false,
                'error' => 'OpenSSL error or invalid CMS signature.',
                'trusted_ca' => false,
                'details' => null
            ];
        }

        $certContent = file_get_contents($certOut);

        $certParsed = openssl_x509_parse($certContent);

        $isTrusted = $this->isCertificateTrusted($certContent);

        return [
            'valid' => $result === self::SIGNATURE_OK,
            'trusted_ca' => $isTrusted,
            'details' => [
                'subject' => $certParsed['subject'] ?? [],
                'issuer' => $certParsed['issuer'] ?? [],
                'valid_from' => date('c', $certParsed['validFrom_time_t']),
                'valid_to' => date('c', $certParsed['validTo_time_t']),
                'serial_number' => $certParsed['serialNumberHex'] ?? null,
            ],
        ];
    }

    /**
     * Sprawdza czy certyfikat pochodzi od zaufanego CA
     */
    protected function isCertificateTrusted(string $certPem): bool
    {
        $verify = openssl_x509_checkpurpose($certPem, X509_PURPOSE_ANY, [$this->caBundlePath]);

        return $verify === true;
    }
}
```