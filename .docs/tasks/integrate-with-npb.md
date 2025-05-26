# Task: Integrate with NBP to fetch Polish bank list using Saloon

## Description
Implement an HTTP client using Saloon to download the official Polish bank list from NBP as an XML file. Parse the XML to extract bank names and codes for further use (e.g., IBAN recognition).

## Steps
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