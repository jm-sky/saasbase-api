# Polish Ministry of Finance VAT API Schema

## Request Schema

### Search Method Request
```json
{
  "method": "search",
  "parameters": {
    "nip": "string (10 digits)",
    "regon": "string (9 or 14 digits)", 
    "account_number": "string (Polish bank account)",
    "date": "string (YYYY-MM-DD format)",
    "company_name": "string (partial name fragment)"
  }
}
```

### Check Method Request
```json
{
  "method": "check",
  "parameters": {
    "nip": "string (10 digits)",
    "regon": "string (9 or 14 digits)",
    "account_number": "string (Polish bank account)",
    "date": "string (YYYY-MM-DD format, required)"
  }
}
```

## Response Schema

### Search Method Response
```json
{
  "result": {
    "entries": [
      {
        "identifier": "string (NIP or REGON)",
        "nip": "string (10 digits)",
        "regon": "string (9 or 14 digits)",
        "name": "string (company name)",
        "status": "string (VAT registration status)",
        "registration_date": "string (YYYY-MM-DD)",
        "deletion_date": "string (YYYY-MM-DD) | null",
        "restoration_date": "string (YYYY-MM-DD) | null",
        "account_numbers": [
          {
            "account_number": "string",
            "bank_name": "string",
            "bank_code": "string"
          }
        ],
        "authorized_clerks": [
          {
            "first_name": "string",
            "last_name": "string",
            "nip": "string",
            "computation_date": "string (YYYY-MM-DD)"
          }
        ],
        "partners": [
          {
            "first_name": "string",
            "last_name": "string",
            "nip": "string",
            "computation_date": "string (YYYY-MM-DD)"
          }
        ],
        "representatives": [
          {
            "first_name": "string", 
            "last_name": "string",
            "nip": "string",
            "computation_date": "string (YYYY-MM-DD)"
          }
        ]
      }
    ],
    "electronic_key": "string (verification identifier)",
    "query_timestamp": "string (ISO 8601 timestamp)",
    "request_id": "string (unique request identifier)"
  }
}
```

### Check Method Response
```json
{
  "result": {
    "confirmation": "YES" | "NO",
    "electronic_key": "string (verification identifier)",
    "query_timestamp": "string (ISO 8601 timestamp)",
    "request_id": "string (unique request identifier)"
  }
}
```

## Error Response Schema
```json
{
  "error": {
    "code": "string (error code)",
    "message": "string (error description)",
    "details": "string (additional error information)"
  }
}
```

## Field Descriptions

### Core Entity Fields
- **nip**: Polish Tax Identification Number (10 digits)
- **regon**: Statistical Number (9 or 14 digits)
- **name**: Official registered company name
- **status**: Current VAT registration status

### Date Fields
- **registration_date**: Date when entity was registered for VAT
- **deletion_date**: Date when VAT registration was deleted (if applicable)
- **restoration_date**: Date when VAT registration was restored (if applicable)
- **computation_date**: Date when specific data was computed/verified

### Account Information
- **account_number**: Polish bank account number (26 digits)
- **bank_name**: Name of the bank holding the account
- **bank_code**: Bank identification code

### Person Information (Clerks, Partners, Representatives)
- **first_name**: Person's first name
- **last_name**: Person's last name  
- **nip**: Person's individual Tax Identification Number
- **computation_date**: Date when person's data was last verified

### Verification Fields
- **electronic_key**: Cryptographic key for verifying query authenticity
- **query_timestamp**: Exact time when query was processed
- **request_id**: Unique identifier for tracking the specific request

## Data Validation Rules

### NIP Validation
- Must be exactly 10 digits
- Must pass mathematical checksum validation
- Leading zeros are preserved

### REGON Validation  
- Must be 9 digits (for physical persons) or 14 digits (for legal entities)
- Must pass mathematical checksum validation

### Account Number Validation
- Must be valid Polish bank account number (26 digits)
- IBAN format: PL + 2 check digits + 24 account digits
- Must pass mathematical validation

### Date Format
- ISO format: YYYY-MM-DD
- Must be valid calendar date
- Historical queries supported for past dates