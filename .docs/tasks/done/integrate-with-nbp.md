# Task: Import Polish banks and provide IBAN-based lookup

Check: https://github.com/globalcitizen/php-iban 

We will import bank routing data (numer rozliczeniowy) and provide a service to get bank info from IBAN. Use **cache** for performance. IBAN lookup works only for **Polish IBANs (PL)**.

---

## ✅ Step 1: Create `Bank` domain

Create model: `Domain/Bank/Models/Bank.php`

### Fields:
- `id`: ULID
- `country`: string ("PL")
- `bank_name`: string
- `bank_code`: string (first 4 digits of routing code)
- `routing_code`: string (8 digits), indexed  
  > `substr($iban, 4, 8)`
- `swift`: string, nullable

---

## ✅ Step 2: Seed data from `nbp_banks.json`

- Create `BankSeeder`:
  - Parse JSON
  - Save entries in DB for "PL" country

---

## ✅ Step 3: Create cached lookup service

Create service: `BankRoutingService`:

- On boot or cache warmup, cache all routing codes as key-value (`routing_code → BankRouting`)
- IBAN lookup:
  1. Validate IBAN 
      - Sanitize - remove spaces
      - Polish IBAN (starts with `PL`, has 28 chars, digits only after PL).
      - Other IBAN's (use *globalcitizen/php-iban*)
  2. Lookup routing code in cache.
  3. Return bank name, SWIFT, and other info.
  4. Return suggestions from *globalcitizen/php-iban* if IBAN is invalid

---

## ✅ Step 4: Create controller endpoint

- `GET /utils/bank-info?iban=PL...`
- Validate input IBAN:
  - Starts with `PL`
  - Has exactly 28 characters
  - Checksum could be validated (optional)
- Return Resource with:
  - `bankName`
  - `swift`

---

## 🧠 Background: Polish IBAN Structure

- Format: `PLkk bbbb ssss cccccccccccccc`
  - `bbbb` – bank code (4 digits)
  - `ssss` – branch/system code (4 digits)
- **Routing code = 8 digits at position 3–10 in IBAN**  
  ```php
  $routingCode = substr($iban, 4, 8);

--- 

## NBP API Integration (maybe later)
### Steps
- Install Saloon PHP HTTP client.
- Create `NbpClient` with base URL `https://www.nbp.pl`.
- Create `BankListRequest` to fetch the bank list XML file from the appropriate NBP URL.
- Use the client and request to download the XML file.
- Parse the XML response with `simplexml_load_string`.
- Extract bank names and codes from the parsed data.
- Store or cache the extracted data for IBAN validation and SWIFT lookup.
- Implement error handling for failed requests.
- Schedule periodic updates (e.g., monthly via cron job).

## Notes
- NBP does not provide a REST API, only downloadable XML/CSV files.
- SWIFT codes may need to be sourced from additional datasets.
