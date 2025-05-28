# Task: Integrate with NBP to fetch Polish bank list using Saloon

## Description
Implement an HTTP client using Saloon to download the official Polish bank list from NBP as an XML file. Parse the XML to extract bank names and codes for further use (e.g., IBAN recognition).

## RAW Data import
At this step we will just import JSON data into system, without integration.
- Create `Bank` domain
- Create `Bank` model
- Create `BankSeeder` to seed from `database/data/nbp_banks.json`
- Create service that returns bank info based on IBAN (polish only). Bank info: bank name, SWIFT. How? By bankCode extracted from IBAN. Maybe we need to save bankCode in dedicated indexed column.
- Create controller and endpoint in /utils, to get bank info based for requested IBAN.

### Bank model
- `id`: string - UUID
- `mfi_id`: string
- `country`: string
- `bank_id`: string
- `name`: string
- `bank_code`: string - maybe, indexed
- `swift`: string nullable - data needed somehow
- `address`: string
- `postal_code`: string
- `city`: string
- `category`: string
- `source_country`: string
- `headquarters_name`: string
- `headquarters_id`: string
- `category_pl`: string
- `regon`: string

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
