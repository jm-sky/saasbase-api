## 🧾 Model: ContractorPreferences

Preferences assigned to a specific contractor. These help pre-fill data during cost invoice creation and OCR processing.

- `id`: `ulid` – Unique identifier of the preferences record
- `contractor_id`: `ulid` – Reference to the contractor
- `default_payment_method_id`: `ulid|null` – Reference to a default payment method
- `default_currency`: `string(3)` – ISO currency code (e.g. "PLN", "EUR")
- `default_payment_terms_days`: `int|null` – Default number of days for payment terms (e.g. 14)
- `default_tags`: `array of strings|null` – Optional default tags to apply to expenses

> Each contractor can have at most one set of preferences.

---

## 💳 Model: PaymentMethod

Defines available payment methods. Methods can be global (shared across all tenants) or tenant-specific.

- `id`: `ulid` – Unique identifier of the payment method
- `tenant_id`: `ulid|null` – If `null`, the method is global; otherwise, it belongs to a specific tenant
- `name`: `string` – Name of the payment method (e.g. "Bank Transfer", "Cash", "Card")
- `default_payment_terms_days`: `int|null` – Suggested default number of days for this payment method

---

### 📝 Note:

The `ContractorPreferences` model is used in two key places:

1. **Invoice creation flow (frontend)** – When a contractor is selected, the form is pre-filled with default values (payment method, currency, terms, tags).
2. **OCR processing** – Helps improve accuracy and pre-filling of invoice data based on known preferences.