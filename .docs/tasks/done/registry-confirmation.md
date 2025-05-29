# RegistryConfirmation

Polymorficzny model potwierdzający dane (adres, konto bankowe, itp.) w zewnętrznych rejestrach publicznych (np. GUS, VIES, Biała Lista VAT).

## Pola:
- id: UUID
- confirmable_id: string
- confirmable_type: string   # np. Address, BankAccount
- type: string               # enum: 'GUS', 'VIES', 'WhiteList'
- payload: json              # dane wejściowe do weryfikacji (np. vat_id, iban)
- result: json               # odpowiedź z rejestru, hash, external_id, match_score itd.
- success: boolean           # czy potwierdzenie było pozytywne
- checked_at: datetime       # kiedy dane były sprawdzane
- created_at: datetime


## Examples
```
{
  "payload": {
    "vat_id": "PL1234567890",
    "iban": "PL60102010260000150202000000"
  },
  "result": {
    "match": true,
    "registry": "white_list",
    "external_id": "REQ-abc123",
    "fetched_at": "2025-05-27T12:30:00Z",
    "confidence": 0.99
  }
}
```