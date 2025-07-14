# REGON API Schema Documentation

## Authentication Schema

### Login Request
```xml
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ns="http://CIS/BIR/PUBL/2014/07">
  <soap:Header/>
  <soap:Body>
    <ns:Zaloguj>
      <ns:pKluczUzytkownika>API_KEY</ns:pKluczUzytkownika>
    </ns:Zaloguj>
  </soap:Body>
</soap:Envelope>
```

### Login Response
```xml
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
  <soap:Body>
    <ZalogujResponse xmlns="http://CIS/BIR/PUBL/2014/07">
      <ZalogujResult>SESSION_ID</ZalogujResult>
    </ZalogujResponse>
  </soap:Body>
</soap:Envelope>
```

## Search Request Schema

### Search by NIP/REGON/KRS
```xml
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ns="http://CIS/BIR/PUBL/2014/07">
  <soap:Header>
    <ns:sid>SESSION_ID</ns:sid>
  </soap:Header>
  <soap:Body>
    <ns:DaneSzukajPodmioty>
      <ns:pParametryWyszukiwania>
        <![CDATA[
        <root>
          <parametry>
            <nip>1234567890</nip>
            <!-- OR -->
            <regon>123456789</regon>
            <!-- OR -->
            <krs>0000123456</krs>
          </parametry>
        </root>
        ]]>
      </ns:pParametryWyszukiwania>
    </ns:DaneSzukajPodmioty>
  </soap:Body>
</soap:Envelope>
```

## Search Response Schema

### Basic Entity Information
```json
{
  "Regon": "string (9 or 14 digits)",
  "Nip": "string (10 digits)",
  "StatusNip": "string",
  "Nazwa": "string (entity name)",
  "Wojewodztwo": "string (voivodeship)",
  "Powiat": "string (county)",
  "Gmina": "string (municipality)",
  "Miejscowosc": "string (city)",
  "KodPocztowy": "string (postal code)",
  "Ulica": "string (street)",
  "NrNieruchomosci": "string (property number)",
  "NrLokalu": "string (apartment number)",
  "Typ": "string (entity type: F=physical, P=legal, LP=local unit)",
  "SilosID": "string (data source identifier)",
  "DataZakonczeniaDzialalnosci": "string (YYYY-MM-DD) | null"
}
```

## Detailed Report Request Schema

### Get Full Report
```xml
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ns="http://CIS/BIR/PUBL/2014/07">
  <soap:Header>
    <ns:sid>SESSION_ID</ns:sid>
  </soap:Header>
  <soap:Body>
    <ns:DanePobierzPelnyRaport>
      <ns:pRegon>123456789</ns:pRegon>
      <ns:pNazwaRaportu>BIR11OsPrawna</ns:pNazwaRaportu>
    </ns:DanePobierzPelnyRaport>
  </soap:Body>
</soap:Envelope>
```

## Detailed Report Response Schemas

### Legal Entity Report (BIR11OsPrawna)
```json
{
  "praw_regon9": "string (9-digit REGON)",
  "praw_nip": "string (10-digit NIP)",
  "praw_statusNip": "string (NIP status)",
  "praw_nazwa": "string (entity name)",
  "praw_nazwaSkrocona": "string (short name)",
  "praw_numerWRejestrzeEwidencji": "string (registry number)",
  "praw_dataWpisuDoREGON": "string (YYYY-MM-DD)",
  "praw_dataRozpoczeciaDzialalnosci": "string (YYYY-MM-DD)",
  "praw_dataWpisuDoRejestru": "string (YYYY-MM-DD)",
  "praw_dataZawieszeniaDzialalnosci": "string (YYYY-MM-DD) | null",
  "praw_dataWznowieniaDzialalnosci": "string (YYYY-MM-DD) | null",
  "praw_dataZakonczeniaDzialalnosci": "string (YYYY-MM-DD) | null",
  "praw_dataSkresleniaDzialalnosci": "string (YYYY-MM-DD) | null",
  "praw_organRejestrowy_Symbol": "string",
  "praw_organRejestrowy_Nazwa": "string",
  "praw_rodzajRejestruEwidencji_Symbol": "string",
  "praw_rodzajRejestruEwidencji_Nazwa": "string",
  "praw_formaFinansowania_Symbol": "string",
  "praw_formaFinansowania_Nazwa": "string",
  "praw_formaWlasnosci_Symbol": "string",
  "praw_formaWlasnosci_Nazwa": "string",
  "praw_organZalozycielski_Symbol": "string",
  "praw_organZalozycielski_Nazwa": "string",
  "praw_adSiedzWoj_Symbol": "string",
  "praw_adSiedzWoj_Nazwa": "string",
  "praw_adSiedzPow_Symbol": "string",
  "praw_adSiedzPow_Nazwa": "string",
  "praw_adSiedzGmina_Symbol": "string",
  "praw_adSiedzGmina_Nazwa": "string",
  "praw_adSiedzKodPocztowy": "string",
  "praw_adSiedzMiejscowoscPoczty_Symbol": "string",
  "praw_adSiedzMiejscowoscPoczty_Nazwa": "string",
  "praw_adSiedzMiejscowosc_Symbol": "string",
  "praw_adSiedzMiejscowosc_Nazwa": "string",
  "praw_adSiedzUlica_Symbol": "string",
  "praw_adSiedzUlica_Nazwa": "string",
  "praw_adSiedzNumerNieruchomosci": "string",
  "praw_adSiedzNumerLokalu": "string",
  "praw_numerTelefonu": "string",
  "praw_numerWewnetrznyTelefonu": "string",
  "praw_numerFaksu": "string",
  "praw_adresEmail": "string",
  "praw_adresStronyinternetowej": "string",
  "praw_adresEmail2": "string",
  "praw_adSiedzKraj_Symbol": "string",
  "praw_adSiedzKraj_Nazwa": "string",
  "praw_podstawowaFormaPrawna_Symbol": "string",
  "praw_podstawowaFormaPrawna_Nazwa": "string",
  "praw_szczegolnaFormaPrawna_Symbol": "string",
  "praw_szczegolnaFormaPrawna_Nazwa": "string",
  "praw_formaWlasnosci_Symbol": "string",
  "praw_formaWlasnosci_Nazwa": "string"
}
```

### Physical Person Report (BIR11OsFizycznaDaneOgolne)
```json
{
  "fiz_regon9": "string (9-digit REGON)",
  "fiz_nip": "string (10-digit NIP)",
  "fiz_statusNip": "string (NIP status)",
  "fiz_nazwa": "string (full name)",
  "fiz_nazwaSkrocona": "string (short name)",
  "fiz_imie": "string (first name)",
  "fiz_nazwisko": "string (last name)",
  "fiz_dataWpisuDoREGON": "string (YYYY-MM-DD)",
  "fiz_dataRozpoczeciaDzialalnosci": "string (YYYY-MM-DD)",
  "fiz_dataZawieszeniaDzialalnosci": "string (YYYY-MM-DD) | null",
  "fiz_dataWznowieniaDzialalnosci": "string (YYYY-MM-DD) | null",
  "fiz_dataZakonczeniaDzialalnosci": "string (YYYY-MM-DD) | null",
  "fiz_dataSkresleniaDzialalnosci": "string (YYYY-MM-DD) | null",
  "fiz_adSiedzWoj_Symbol": "string",
  "fiz_adSiedzWoj_Nazwa": "string",
  "fiz_adSiedzPow_Symbol": "string",
  "fiz_adSiedzPow_Nazwa": "string",
  "fiz_adSiedzGmina_Symbol": "string",
  "fiz_adSiedzGmina_Nazwa": "string",
  "fiz_adSiedzKodPocztowy": "string",
  "fiz_adSiedzMiejscowoscPoczty_Symbol": "string",
  "fiz_adSiedzMiejscowoscPoczty_Nazwa": "string",
  "fiz_adSiedzMiejscowosc_Symbol": "string",
  "fiz_adSiedzMiejscowosc_Nazwa": "string",
  "fiz_adSiedzUlica_Symbol": "string",
  "fiz_adSiedzUlica_Nazwa": "string",
  "fiz_adSiedzNumerNieruchomosci": "string",
  "fiz_adSiedzNumerLokalu": "string",
  "fiz_numerTelefonu": "string",
  "fiz_numerWewnetrznyTelefonu": "string",
  "fiz_numerFaksu": "string",
  "fiz_adresEmail": "string",
  "fiz_adresStronyinternetowej": "string",
  "fiz_adSiedzKraj_Symbol": "string",
  "fiz_adSiedzKraj_Nazwa": "string"
}
```

### PKD Activity Codes Report (BIR11OsPrawnaPkd)
```json
[
  {
    "praw_pkdKod": "string (PKD code)",
    "praw_pkdNazwa": "string (activity description)",
    "praw_pkdPrzewazajace": "string (1=main activity, 0=secondary)"
  }
]
```

## Error Response Schema
```xml
<soap:Fault>
  <faultcode>soap:Client</faultcode>
  <faultstring>Error description</faultstring>
  <detail>
    <ErrorInfo>
      <ErrorCode>error_code</ErrorCode>
      <ErrorMessage>Detailed error message</ErrorMessage>
    </ErrorInfo>
  </detail>
</soap:Fault>
```

## Common Error Codes
- `1`: Invalid session ID
- `2`: Invalid API key
- `3`: Service temporarily unavailable
- `4`: Invalid request parameters
- `5`: Report not found
- `6`: Entity not found

## Field Validation Rules

### REGON Validation
- **9 digits**: Physical persons and local units
- **14 digits**: Legal entities
- Must pass mathematical checksum validation

### NIP Validation
- **10 digits**: Polish Tax Identification Number
- Must pass mathematical checksum validation

### KRS Validation
- **10 digits**: Court Registration System number
- Format: 0000XXXXXX (leading zeros required)

## Report Types and Usage

### Available Reports
1. **BIR11OsFizycznaDaneOgolne**: General data for physical persons
2. **BIR11OsFizycznaDzialalnoscCeidg**: CEIDG registration data
3. **BIR11OsFizycznaDzialalnoscRolnicza**: Agricultural activity data
4. **BIR11OsPrawna**: Legal entity general data
5. **BIR11OsPrawnaPkd**: Business activity classification codes
6. **BIR11OsPrawnaListaJednLokalnych**: List of local organizational units
7. **BIR11JednLokalnaOsPrawnej**: Local unit detailed data
8. **BIR11OsPrawnaSpCywilnaWspolnicy**: Civil partnership members
9. **BIR11TypPodmiotu**: Entity type classification