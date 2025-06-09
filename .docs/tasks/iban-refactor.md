# IBAN Enrichment Strategy

We use multiple sources to enrich IBANs with bank-related information such as bank name, currency, and BIC/SWIFT. Here’s our current and recommended approach.

---

## 🧩 Current Components

This controller `app/Domain/Utils/Controllers/BankInfoController.php` uses this `app/Services/IbanInfo/IbanInfoService.php` service.

- **Banks table** (local database): 
  - Contains static list of Polish banks with names and SWIFT codes.
  - Can become stale over time if not updated.

- **IbanApi integration**:
  - External service that returns reliable, real-time data for IBANs including BIC, bank name, and currency.
  - Supports a broad range of countries.

- **`globalcitizen/php-iban` package**:
  - Local PHP library used to validate and parse IBAN structure.
  - Can derive country code and default currency from IBAN.
  - **Can also provide suggestions for fixing invalid IBANs** (e.g. formatting issues, missing characters).
  - Not actively maintained; should not be relied upon for up-to-date bank details.

---

## ✅ Recommended Strategy

### Keep All Three, with Adjusted Roles

1. **Primary Source: IbanApi**
   - Use as the main source of truth.
   - Query on demand when data is missing or outdated.
   - Cache responses in Redis and persist to DB.

2. **Secondary: Local Banks Table**
   - Use only as a fallback or for non-user-specific logic.
   - Optionally update it periodically from trusted sources.

3. **Supportive: `php-iban` Package**
   - Use for structural validation and country-level info.
   - Use suggestions for minor IBAN corrections or validation feedback.
   - Don’t rely on it for bank-specific information.

---

## 💾 Database Persistence

Yes — save enriched IBAN data to a database table:

```plaintext
ibans: [
  iban,
  bank_name,
  bic,
  currency,
  country_code,
  validated_at,
  source (api|manual),
  raw_api_response (optional),
  ...
]
```

- This enables re-use without hitting the API.
- Add logic to re-validate records after a certain time (e.g. 30 days).
- Store raw API response (optionally) for debugging/auditing.

---

## ⚙️ Fallback Workflow

1. Check Redis cache.
2. If not found, check the `ibans` DB table.
3. If still missing or stale, call IbanApi.
4. Store result to Redis and DB.

---

## 🧠 Optimization Note

We can extract bank info for **many IBANs** using just one valid IBAN. This is possible because IBANs from the same country often share a **bank code** (e.g., for Polish IBANs: digits 3–10). Once we enrich one IBAN for a given bank code, we can cache or store bank details and reuse them for other IBANs with the same prefix.

This strategy reduces API calls and improves response time when processing multiple IBANs.

---

## 🔒 Additional Notes

- Rate-limit API usage to avoid unnecessary costs.
- Consider background jobs to re-check stale IBANs periodically.
- Log unexpected or inconsistent responses for future investigation.


# 🔄 IBAN Lookup Flow (Enrichment Logic)

## 0) ✅ Validate Using `php-iban`
- Check if the IBAN is structurally valid (length, check digits).
- Parse country code, check digit, bank code (if applicable).
- Optionally use suggestions for auto-correction of slightly invalid IBANs (e.g. formatting or length errors).

---

## 1) 🔍 Check Redis Cache
- Use a key like: `iban:{IBAN}` or `iban:{country}:{bank_code}`
- Return if found.
- Recommended TTL: **7–30 days** depending on traffic and data volatility.
  - For example: 
    - Known trusted IBANs → 30d
    - New / rare IBANs → 7d
- Keep cache small and rotate if needed.

---

## 2) 🧾 Check Database
- Look for a record in the `ibans` table.
- Check if it’s **recent enough** to be trusted:
  - Recommended `validated_at` max age: **30–90 days**
- If recent, return from DB and re-cache in Redis.

---

## 3) 🌍 Query IbanApi
- Fallback when Redis and DB miss or data is outdated.
- Retrieve enriched IBAN info:
  - Bank name
  - BIC/SWIFT
  - Currency
  - Country code
- Handle errors and log failures for review.

---

## 4) 💾 Cache and Save
- Save enriched result to:
  - **Redis** (for fast access)
  - **Database** (`ibans` table with `validated_at` timestamp)
- Optionally store raw API response for audit/debugging.

---

## 💡 Bonus Optimization

- Use **bank code** extracted from one IBAN to enrich others with the same prefix (e.g., digits 3–10 in Polish IBANs).
- Cache this per-bank info separately:
  - Redis key: `ibaninfo:{country}:{bank_code}`
  - DB table: `bank_codes` or similar

This lets you enrich multiple IBANs with a single API call.