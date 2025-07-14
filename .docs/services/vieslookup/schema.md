# VIES SOAP API Schema Documentation

## SOAP Envelope Structure

### Namespace Definitions
```xml
xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:urn="urn:ec.europa.eu:taxud:vies:services:checkVat:types"
```

## Request Schemas

### Simple VAT Validation Request
```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
                  xmlns:urn="urn:ec.europa.eu:taxud:vies:services:checkVat:types">
  <soapenv:Header/>
  <soapenv:Body>
    <urn:checkVat>
      <urn:countryCode>DE</urn:countryCode>
      <urn:vatNumber>123456789</urn:vatNumber>
    </urn:checkVat>
  </soapenv:Body>
</soapenv:Envelope>
```

### Approximate VAT Validation Request (with Requester Info)
```xml
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
                  xmlns:urn="urn:ec.europa.eu:taxud:vies:services:checkVat:types">
  <soapenv:Header/>
  <soapenv:Body>
    <urn:checkVatApprox>
      <urn:countryCode>DE</urn:countryCode>
      <urn:vatNumber>123456789</urn:vatNumber>
      <urn:requesterCountryCode>PL</urn:requesterCountryCode>
      <urn:requesterVatNumber>1234567890</urn:requesterVatNumber>
    </urn:checkVatApprox>
  </soapenv:Body>
</soapenv:Envelope>
```

## Response Schemas

### Successful Validation Response (Valid VAT)
```xml
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <checkVatResponse xmlns="urn:ec.europa.eu:taxud:vies:services:checkVat:types">
      <countryCode>DE</countryCode>
      <vatNumber>123456789</vatNumber>
      <requestDate>2024-01-15+01:00</requestDate>
      <valid>true</valid>
      <name>Example Company GmbH</name>
      <address>Beispielstrasse 123&#x0A;10115 Berlin&#x0A;DEUTSCHLAND</address>
    </checkVatResponse>
  </soap:Body>
</soap:Envelope>
```

### Invalid VAT Number Response
```xml
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <checkVatResponse xmlns="urn:ec.europa.eu:taxud:vies:services:checkVat:types">
      <countryCode>DE</countryCode>
      <vatNumber>000000000</vatNumber>
      <requestDate>2024-01-15+01:00</requestDate>
      <valid>false</valid>
      <name>---</name>
      <address>---</address>
    </checkVatResponse>
  </soap:Body>
</soap:Envelope>
```

### Approximate Validation Response (Enhanced)
```xml
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <checkVatApproxResponse xmlns="urn:ec.europa.eu:taxud:vies:services:checkVat:types">
      <countryCode>DE</countryCode>
      <vatNumber>123456789</vatNumber>
      <requestDate>2024-01-15+01:00</requestDate>
      <valid>true</valid>
      <name>Example Company GmbH</name>
      <address>Beispielstrasse 123&#x0A;10115 Berlin&#x0A;DEUTSCHLAND</address>
      <requestIdentifier>WAPIaaaakkk3333</requestIdentifier>
      <traderName>Example Company GmbH</traderName>
      <traderCompanyType>GESELLSCHAFT MIT BESCHRAENKTER HAFTUNG</traderCompanyType>
      <traderAddress>Beispielstrasse 123&#x0A;10115 Berlin&#x0A;DEUTSCHLAND</traderAddress>
    </checkVatApproxResponse>
  </soap:Body>
</soap:Envelope>
```

## Error Response Schema (SOAP Fault)

### General SOAP Fault Structure
```xml
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <soap:Fault>
      <faultcode>soap:Server</faultcode>
      <faultstring>INVALID_INPUT</faultstring>
      <detail>
        <n1:checkVatFault xmlns:n1="urn:ec.europa.eu:taxud:vies:services:checkVat:types">
          <n1:faultCode>INVALID_INPUT</n1:faultCode>
          <n1:faultString>The provided CountryCode is invalid or the VAT number is empty</n1:faultString>
        </n1:checkVatFault>
      </detail>
    </soap:Fault>
  </soap:Body>
</soap:Envelope>
```

## Field Specifications

### Request Fields

#### countryCode
- **Type**: String (2 characters)
- **Pattern**: `^[A-Z]{2}$`
- **Description**: EU member state country code
- **Required**: Yes
- **Valid Values**: AT, BE, BG, HR, CY, CZ, DK, EE, FI, FR, DE, GR, HU, IE, IT, LV, LT, LU, MT, NL, PL, PT, RO, SK, SI, ES, SE

#### vatNumber
- **Type**: String
- **Pattern**: `^[0-9A-Za-z\+\*\.]+$`
- **Description**: VAT number without country prefix
- **Required**: Yes
- **Max Length**: Varies by country (typically 8-12 characters)

#### requesterCountryCode (Approximate validation only)
- **Type**: String (2 characters)
- **Pattern**: `^[A-Z]{2}$`
- **Description**: Country code of the entity making the request
- **Required**: Yes (for approximate validation)

#### requesterVatNumber (Approximate validation only)
- **Type**: String
- **Description**: VAT number of the entity making the request
- **Required**: Yes (for approximate validation)

### Response Fields

#### countryCode
- **Type**: String
- **Description**: Echo of the requested country code

#### vatNumber
- **Type**: String
- **Description**: Echo of the requested VAT number

#### requestDate
- **Type**: String (DateTime with timezone)
- **Format**: `YYYY-MM-DD+HH:MM`
- **Description**: Timestamp when the validation was performed

#### valid
- **Type**: Boolean
- **Description**: Whether the VAT number is valid and active

#### name
- **Type**: String
- **Description**: Registered company name (if available and valid)
- **Note**: Returns "---" if VAT is invalid or name not available

#### address
- **Type**: String
- **Description**: Registered company address (if available and valid)
- **Note**: Returns "---" if VAT is invalid or address not available
- **Format**: Multi-line string with &#x0A; as line separators

#### requestIdentifier (Approximate validation only)
- **Type**: String
- **Description**: Unique consultation number for audit purposes
- **Format**: Alphanumeric string (typically 15-20 characters)
- **Usage**: Keep for tax administration proof

#### traderName (Approximate validation only)
- **Type**: String
- **Description**: Enhanced trader name information

#### traderCompanyType (Approximate validation only)
- **Type**: String
- **Description**: Legal form/type of the company
- **Example**: "GESELLSCHAFT MIT BESCHRAENKTER HAFTUNG"

#### traderAddress (Approximate validation only)
- **Type**: String
- **Description**: Complete formatted address including country name

## Error Codes and Fault Types

### VIES Fault Codes
1. **INVALID_INPUT**
   - Description: Invalid country code or empty VAT number
   - Action: Check input parameters

2. **INVALID_REQUESTER_INFO**
   - Description: Invalid requester country code or VAT number
   - Action: Verify requester information (approximate validation only)

3. **SERVICE_UNAVAILABLE**
   - Description: VIES service is temporarily unavailable
   - Action: Retry later

4. **MS_UNAVAILABLE**
   - Description: Member state system is unavailable
   - Action: Retry later or try web interface

5. **TIMEOUT**
   - Description: Request timed out
   - Action: Retry with shorter timeout

6. **VAT_BLOCKED**
   - Description: VAT number is blocked
   - Action: Contact tax authorities

7. **IP_BLOCKED**
   - Description: IP address is temporarily blocked
   - Action: Wait or contact support

8. **GLOBAL_MAX_CONCURRENT_REQ**
   - Description: Too many concurrent requests globally
   - Action: Implement retry logic with backoff

9. **GLOBAL_MAX_CONCURRENT_REQ_TIME**
   - Description: Global concurrent request time limit exceeded
   - Action: Implement request throttling

10. **MS_MAX_CONCURRENT_REQ**
    - Description: Member state concurrent request limit exceeded
    - Action: Reduce request frequency for specific country

11. **MS_MAX_CONCURRENT_REQ_TIME**
    - Description: Member state concurrent request time limit exceeded
    - Action: Implement country-specific throttling

## Test VAT Numbers

For testing purposes, VIES provides special test numbers:

| VAT Number | Expected Result |
|------------|----------------|
| 100 | Valid request with Valid VAT Number |
| 200 | Valid request with Invalid VAT Number |
| 201 | Error: INVALID_INPUT |
| 202 | Error: INVALID_REQUESTER_INFO |
| 300 | Error: SERVICE_UNAVAILABLE |

Usage example:
```xml
<urn:countryCode>DE</urn:countryCode>
<urn:vatNumber>100</urn:vatNumber>
```

## Character Encoding Notes

- **XML Encoding**: UTF-8
- **Line Separators**: Addresses use `&#x0A;` (Line Feed) for line breaks
- **Special Characters**: XML-encoded (e.g., `&amp;`, `&lt;`, `&gt;`)
- **Umlauts and Accents**: Properly encoded in UTF-8

## WSDL Information

- **WSDL URL**: `http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl`
- **Target Namespace**: `urn:ec.europa.eu:taxud:vies:services:checkVat`
- **Types Namespace**: `urn:ec.europa.eu:taxud:vies:services:checkVat:types`
- **Service Name**: `checkVatService`
- **Port Name**: `checkVatPort`