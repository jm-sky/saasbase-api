# ✅ Zadanie: Confirm User Identity (podpis EPUAP)

## 🎯 Cel funkcji
Pozwolić użytkownikowi potwierdzić swoją tożsamość przez podpisanie dokumentu PDF przy użyciu EPUAP (kwalifikowany podpis elektroniczny). Dokument jest generowany przez system, a podpis jest później automatycznie weryfikowany.

---

## 🔁 Przebieg (UX / Flow)

1. Użytkownik otwiera stronę **Potwierdzenie tożsamości**.
2. Widzi przycisk: **„Pobierz oświadczenie do podpisania”**.
3. Kliknięcie:
   - wysyła request do backendu,
   - backend generuje PDF z danymi użytkownika oraz tokenem i timestampem,
   - plik PDF trafia do kolekcji `identity_confirmation_template` w Media Library użytkownika.
4. Frontend dostaje URL do pliku i wyświetla przycisk **„Pobierz PDF”** (teraz XML)
5. Użytkownik podpisuje PDF (xml) przez EPUAP.
6. Użytkownik przesyła podpisany plik przez formularz (upload).
7. Backend:
   - weryfikuje podpis (PAdES),
   - porównuje dane w pliku z profilem użytkownika,
   - jeśli OK: zapisuje plik w `identity_confirmation_final` i czyści poprzedni,
   - zwraca status (zaufany / niezaufany) + szczegóły na frontend.

---

## 🧱 Dane zawarte w PDF/XML

Generowane na podstawie `auth()->user()`:

- `first_name`
- `last_name`
- `birth_date`
- `pesel`
- `confirmation_token` (UUID lub losowy hash)
- `generated_at` (timestamp UTC)
- `app_name` (np. `config('app.name')`)

📄 Przykład tekstu w PDF:
> „Oświadczam, że ja, **Jan Kowalski** (PESEL: XXX), urodzony dnia YYY, potwierdzam moją tożsamość na potrzeby aplikacji **TwojaAppka**.  
> Data i godzina wygenerowania: 2025-06-06 14:32  
> Unikalny identyfikator: **IDENTITY-123e4567-e89b-12d3-a456-426614174000**”

---

## 📂 Kolekcje Media (Spatie)

- `identity_confirmation_template` – PDF do podpisania, 1 plik (ostatni)
- `identity_confirmation_final` – podpisany PDF, 1 plik (można wersjonować)

---

## ⚙️ Endpointy API (Laravel) – **Status implementacji**

### `POST /api/v1/identity/confirmation/template`
- [x] **Route istnieje** (`routes/api/user.php`)
- [x] **Kontroler istnieje** (`IdentityConfirmationController@generateTemplate`)
- [ ] **Logika generowania PDF/XML, zapis do Media Library, zwrot file_url/expiry** *(do zaimplementowania)*

### `POST /api/v1/identity/confirmation/submit`
- [x] **Route istnieje** (`routes/api/user.php`)
- [x] **Kontroler istnieje** (`IdentityConfirmationController@submitSigned`)
- [ ] **Logika uploadu, weryfikacji podpisu, porównania danych, zapisania do Media Library, odpowiedź** *(do zaimplementowania)*

---

## 🧰 Wewnętrzna klasa serwisowa – **Status**
- [ ] `App\Services\PdfSignatureVerifierService` – **do zaimplementowania**

---

## 🖼 Frontend (Vue 3 + Axios)

1. `api.post('/identity/confirmation/template')` (api używa baseUrl) 
2. Pokazanie linku do pobrania PDF
3. Formularz uploadu podpisanego pliku
4. `api.postForm('/identity/confirmation/submit', { file })`
5. Wyświetlenie statusu i szczegółów podpisu

---

## 🧪 Testy – **Status**
- [ ] Testy backendu dla powyższych endpointów i logiki – **do zaimplementowania**

---

## 🧭 Możliwe rozszerzenia

- ⏳ Token z TTL (np. 1h ważności)
- ♻️ Możliwość wygenerowania nowego szablonu
- 🧾 Historia weryfikacji (wersjonowanie podpisów)

# ✅ Identity Confirmation (XML + Podpis)

Użytkownik potwierdza swoją tożsamość, podpisując wygenerowany plik XML przy użyciu podpisu kwalifikowanego (np. przez EPUAP), a następnie przesyła go z powrotem. System waliduje strukturę, podpis oraz dane.

---

## 🧾 XML Example

```xml
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="https://saasbase.madeyski.org/xml/identity/v1/identity-confirmation.xsl"?>
<IdentityConfirmation xmlns="https://saasbase.madeyski.org/xml/identity/v1"
                      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                      xsi:schemaLocation="https://saasbase.madeyski.org/xml/identity/v1 https://saasbase.madeyski.org/xml/identity/v1/identity-confirmation.xsd">

  <FirstName>Jan</FirstName>
  <LastName>Kowalski</LastName>
  <FullName>Jan Kowalski</FullName>
  <BirthDate>1985-04-12</BirthDate>
  <PESEL>85041212345</PESEL>
  <GeneratedAt>2025-06-06T14:32:00Z</GeneratedAt>
  <ConfirmationToken>01HX7ZEGZ6ECZT8B3BG9AXW53H</ConfirmationToken>
  <ApplicationName>SaaSBass</ApplicationName>

</IdentityConfirmation>
```

---

## 📐 XML Schema (XSD)

- **Frontend path**: `public/xml/identity/v1/identity-confirmation.xsd`
- **Public URL**: `https://saasbase.madeyski.org/xml/identity/v1/identity-confirmation.xsd`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
           targetNamespace="https://saasbase.madeyski.org/xml/identity/v1"
           xmlns="https://saasbase.madeyski.org/xml/identity/v1"
           elementFormDefault="qualified">

  <xs:element name="IdentityConfirmation">
    <xs:complexType>
      <xs:sequence>
        <xs:element name="FirstName" type="xs:string"/>
        <xs:element name="LastName" type="xs:string"/>
        <xs:element name="FullName" type="xs:string"/>
        <xs:element name="BirthDate" type="xs:date"/>
        <xs:element name="PESEL" type="xs:string"/>
        <xs:element name="GeneratedAt" type="xs:dateTime"/>
        <xs:element name="ConfirmationToken" type="xs:string"/>
        <xs:element name="ApplicationName" type="xs:string"/>
      </xs:sequence>
    </xs:complexType>
  </xs:element>

</xs:schema>
```

## XML styles (XSL)

```
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
      xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
      xmlns:ns="https://saasbase.madeyski.org/xml/identity/v1"
      exclude-result-prefixes="ns">

  <xsl:output method="html" encoding="UTF-8"/>

  <xsl:template match="/">
    <html>
      <head>
        <title>Oświadczenie tożsamości</title>
        <style>
          body { font-family: sans-serif; padding: 2em; }
          h1 { font-size: 1.5em; margin-bottom: 1em; }
          ul { list-style: none; padding: 0; }
          li { margin-bottom: 0.5em; }
          strong { display: inline-block; width: 180px; }
        </style>
      </head>
      <body>
        <h1>Oświadczenie tożsamości</h1>
        <ul>
          <li><strong>Imię:</strong> <xsl:value-of select="ns:IdentityConfirmation/ns:FirstName"/></li>
          <li><strong>Nazwisko:</strong> <xsl:value-of select="ns:IdentityConfirmation/ns:LastName"/></li>
          <li><strong>Pełne imię i nazwisko:</strong> <xsl:value-of select="ns:IdentityConfirmation/ns:FullName"/></li>
          <li><strong>Data urodzenia:</strong> <xsl:value-of select="ns:IdentityConfirmation/ns:BirthDate"/></li>
          <li><strong>PESEL:</strong> <xsl:value-of select="ns:IdentityConfirmation/ns:PESEL"/></li>
          <li><strong>Data wygenerowania:</strong> <xsl:value-of select="ns:IdentityConfirmation/ns:GeneratedAt"/></li>
          <li><strong>Token potwierdzający:</strong> <xsl:value-of select="ns:IdentityConfirmation/ns:ConfirmationToken"/></li>
          <li><strong>Nazwa aplikacji:</strong> <xsl:value-of select="ns:IdentityConfirmation/ns:ApplicationName"/></li>
        </ul>
      </body>
    </html>
  </xsl:template>

</xsl:stylesheet> 
```

Wersja poprawiona z kolorami:

```
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
      xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
      xmlns:ns="https://saasbase.madeyski.org/xml/identity/v1"
      exclude-result-prefixes="ns">

  <xsl:output method="html" encoding="UTF-8"/>

  <xsl:template match="/">
    <html>
      <head>
        <title>Oświadczenie tożsamości</title>
        <style>
          body {
            font-family: sans-serif;
            margin: 2em;
            color: #222;
          }
          .identity-box {
            border: 1px solid #ccc;
            padding: 1.5em;
            border-radius: 8px;
            box-shadow: 0 0 10px #ddd;
            max-width: 600px;
          }
          h1 {
            font-size: 1.8em;
            margin-bottom: 0.5em;
            /* fallback color */
            color: #3a69f4;
            /* preferred color */
            color: oklch(58.8% 0.158 241.966);
          }
          p.description {
            margin-bottom: 2em;
            font-size: 1em;
            color: #555;
          }
          dl {
            margin: 0;
          }
          dt {
            font-weight: bold;
            margin-top: 1em;
            color: #3a69f4;
            color: oklch(58.8% 0.158 241.966);
          }
          dd {
            margin-left: 1em;
            margin-bottom: 0.5em;
          }
          p.footer {
            font-size: 0.8em;
            color: #666;
            margin-top: 2em;
          }
          p.signature-note {
            font-style: italic;
            margin-top: 1em;
            color: #666;
            font-size: 0.9em;
          }
        </style>
      </head>
      <body>
        <div class="identity-box">
          <h1>Oświadczenie tożsamości</h1>
          <p class="description">
            Niniejszy dokument stanowi potwierdzenie tożsamości osoby fizycznej, wygenerowane przez system <strong>SaaSBase</strong>.
          </p>

          <dl>
            <dt>Imię:</dt>
            <dd><xsl:value-of select="ns:IdentityConfirmation/ns:FirstName"/></dd>

            <dt>Nazwisko:</dt>
            <dd><xsl:value-of select="ns:IdentityConfirmation/ns:LastName"/></dd>

            <dt>Pełne imię i nazwisko:</dt>
            <dd><xsl:value-of select="ns:IdentityConfirmation/ns:FullName"/></dd>

            <dt>Data urodzenia:</dt>
            <dd><xsl:value-of select="ns:IdentityConfirmation/ns:BirthDate"/></dd>

            <dt>PESEL:</dt>
            <dd><xsl:value-of select="ns:IdentityConfirmation/ns:PESEL"/></dd>

            <dt>Data wygenerowania:</dt>
            <dd><xsl:value-of select="ns:IdentityConfirmation/ns:GeneratedAt"/></dd>

            <dt>Token potwierdzający:</dt>
            <dd><xsl:value-of select="ns:IdentityConfirmation/ns:ConfirmationToken"/></dd>

            <dt>Nazwa aplikacji:</dt>
            <dd><xsl:value-of select="ns:IdentityConfirmation/ns:ApplicationName"/></dd>
          </dl>

          <p class="footer">
            Dokument wygenerowany automatycznie – nie wymaga podpisu ręcznego.
          </p>

          <p class="signature-note">
            <em>Dokument może zostać podpisany elektronicznie zgodnie z art. 20c ustawy o informatyzacji działalności podmiotów realizujących zadania publiczne.</em>
          </p>
        </div>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet> 
```

---

## 🔁 Flow

1. Użytkownik wchodzi na stronę potwierdzania tożsamości.
2. Kliknięcie "Pobierz plik do podpisu":
   - Backend generuje XML na podstawie danych użytkownika.
   - `ConfirmationToken` to np. `auth()->user()->ulid()`.
   - XML trafia do Media Library (`identity_confirmation_template`).
3. Użytkownik podpisuje plik (np. przez EPUAP) i robi upload.
4. Backend:
   - Waliduje strukturę XML (`XSD`).
   - Weryfikuje podpis (App\Services\XmlSignatureVerifierService).
   - Porównuje dane z profilem użytkownika.
   - Zapisuje do `identity_confirmation_final`, usuwa poprzedni szablon.
   - Zwraca status (zaufany / niezaufany) i szczegóły na frontend.

---

## 🧱 Pola w XML

| Pole               | Typ         | Opis                                           |
|--------------------|-------------|------------------------------------------------|
| `FirstName`        | `xs:string` | Imię użytkownika                               |
| `LastName`         | `xs:string` | Nazwisko użytkownika                           |
| `FullName`         | `xs:string` | Pełne imię i nazwisko                          |
| `BirthDate`        | `xs:date`   | Data urodzenia                                 |
| `PESEL`            | `xs:string` | Numer PESEL                                    |
| `GeneratedAt`      | `xs:dateTime`| Data wygenerowania XML                         |
| `ConfirmationToken`| `xs:string` | ULID użytkownika (lub losowy token)            |
| `ApplicationName`  | `xs:string` | Nazwa aplikacji (`config('app.name')`)         |

---

## 🧭 Możliwości rozszerzenia

- Wersjonowanie schematów (`/v2/...`) przy zmianie struktury.
- TTL tokenu – walidacja `GeneratedAt`.
- Historia potwierdzeń (wersjonowanie podpisanych XML).
- Fallback do PDF dla integracji wymagających innego formatu.

---

## XmlValidatorService 

```php
namespace App\Services;

use DOMDocument;
use RuntimeException;

class XmlValidatorService
{
    public function validate(string $xmlContent, string $xsdPath): void
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        if (!$dom->loadXML($xmlContent)) {
            throw new RuntimeException('Niepoprawna struktura XML: ' . $this->formatErrors());
        }

        if (!$dom->schemaValidate($xsdPath)) {
            throw new RuntimeException('XML niezgodny z XSD: ' . $this->formatErrors());
        }

        libxml_clear_errors();
    }

    protected function formatErrors(): string
    {
        $errors = libxml_get_errors();
        return collect($errors)
            ->map(fn($error) => trim($error->message))
            ->implode('; ');
    }
}
```

### Przykład użycia

```
use App\Services\XmlValidatorService;

$xml = file_get_contents($uploadedFile->getPathname());
$xsdPath = public_path('xml/identity/v1/identity-confirmation.xsd');

app(XmlValidatorService::class)->validate($xml, $xsdPath);
```

---

> **Podsumowanie:**
> - Wszystkie wymagane **route'y i kontrolery są już obecne** w projekcie.
> - **Cała logika biznesowa** (generowanie plików, weryfikacja podpisu, obsługa Media Library, testy) **pozostaje do zaimplementowania**.


## Plan

### 🚦 AI Agent Implementation Steps (XML Flow)
> **Note:** Use XML (not PDF) for all document generation and verification.

#### 1. Endpoint: Generate XML Template
- [x] Implement logic in `IdentityConfirmationController@generateTemplate`:
    - [x] Generate XML file with user data (see schema and example above).
    - [x] Use current authenticated user for data fields.
    - [x] Store XML in Media Library under `identity_confirmation_template`.
    - [x] Return public file URL and expiry in response.

#### 2. Endpoint: Submit Signed XML
- [x] Implement logic in `IdentityConfirmationController@submitSigned`:
    - [x] Accept uploaded signed XML file (multipart/form-data).
    - [x] Validate XML structure against XSD schema (`public/xml/identity/v1/identity-confirmation.xsd`).
    - [x] Verify electronic signature (use or implement `App\Services\PdfSignatureVerifierService`).
    - [x] Extract and compare data from XML with current user profile.
    - [x] If valid, store signed XML in `identity_confirmation_final` and remove previous template.
    - [x] Return status (`verified`/`unverified`), field match info, and signature details in response.
    - [x] Optionally create/update UserPersonalData if PESEL is missing but present in signature.

#### 3. Service Classes
- [x] Use `App\Services\PdfSignatureVerifierService` for signature validation and data extraction.
- [x] Inline XSD validation logic (no separate XmlValidatorService needed).

#### 4. Tests
- [ ] Add feature tests for both endpoints:
    - [ ] XML generation and download.
    - [ ] Upload, validation, and verification of signed XML.
    - [ ] Data comparison and media storage logic.

#### 5. Documentation
- [ ] Update OpenAPI/Scribe docs for both endpoints.
- [ ] Ensure all request/response examples use XML, not PDF.

---

> **Agent Guidance:**
> As of {TODAY}, all backend logic for the XML flow is implemented. Proceed with tests and documentation next.  
> **Do not implement PDF logic. Use XML only.**
