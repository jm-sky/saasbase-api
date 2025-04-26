## [Contractor]

Represents a company, person, or foundation that the tenant collaborates with — e.g., clients, suppliers, subcontractors. Contractors can be tagged, have a logo, and participate in invoicing either as buyers or suppliers.

### Key Fields
- `id`: UUID primary key
- `tenant_id`: UUID, references the tenant that owns this contractor
- `name`: Full legal or personal name of the contractor
- `type`: Defines the nature of the contractor  
  - Possible values: `"company"`, `"person"`, `"foundation"`
- `tax_id`: Tax identification number (nullable)  
  - **If `country_code` is "PL"** – must be a valid Polish NIP  
  - **Otherwise** – any valid local tax ID
- `email`: Optional contact email (nullable)
- `phone`: Optional contact phone number (nullable)
- `description`: Optional free-form notes (nullable)
- `preferences`: JSON object for storing additional contractor-specific settings (nullable)  
  Examples:  
  - Default payment terms
  - Preferred currency
- `is_supplier`: Boolean, whether this contractor supplies products or services
- `is_buyer`: Boolean, whether this contractor purchases products or services
- `created_at`: Timestamp of contractor creation
- `updated_at`: Timestamp of last contractor update
- `deleted_at`: Timestamp for soft deletes (nullable)

### Relationships
- Belongs to one `Tenant`
- Has many `ContractorAddresses`
- Has many `ContractorContacts`
- Has many `ContractorBankAccounts`
- Has many `Tags` (many-to-many relationship)
- Can have one logo attachment (image)
- Can have multiple document attachments (e.g., agreements)

### Validation / Business Logic
- `tax_id` is required only if needed by local regulations; validated if `country_code` is "PL"
- Contractor can be marked both as `is_supplier` and `is_buyer` if necessary
- `type` determines contextual behavior (e.g., "foundation" might require different documents)

### Notes
- Soft deletion enabled via `deleted_at`
- `preferences` field allows tenant-specific customizations without changing the schema
- Tags allow categorizing contractors for easier filtering (e.g., "vip-client", "new-lead")
- Logo improves visual identification in the system
- We can load company info based on `tax_id` (NIP) from external services (i.e. for Poland from GUS, REGON, Vat Payers White List)


## [ContractorAddress]

Represents a physical address associated with a contractor. Contractors can have multiple addresses (e.g., headquarters, warehouse, billing address).

### Key Fields
- `id`: UUID primary key
- `contractor_id`: UUID, references the contractor this address belongs to
- `label`: Short label describing the address type (e.g., "Headquarters", "Billing", "Warehouse")
- `country_code`: ISO 3166-1 alpha-2 country code (e.g., "PL", "DE")
- `street`: Street address (including house/building number if needed)
- `city`: City name
- `zip`: Postal code (nullable)
- `is_default`: Boolean indicating if this is the default address for the contractor

### Relationships
- Belongs to one `Contractor`

### Notes
- Each contractor can have multiple addresses, but typically only one marked as `is_default`
- No built-in constraints on uniqueness of `label` per contractor (handled optionally at the application level)

---

## [ContractorBankAccount]

Represents a bank account linked to a contractor.  
This is used for financial operations such as issuing invoices or payments.

### Key Fields
- `id`: UUID primary key
- `contractor_id`: UUID, references the contractor this bank account belongs to
- `bank_name`: Name of the bank
- `iban`: International Bank Account Number (IBAN)
- `currencies`: Comma-separated list of supported currencies (e.g., "PLN,EUR")
- `is_default`: Boolean indicating if this is the default bank account
- `white_list_checked_at`: Timestamp of the last white list (compliance) check (nullable)
- `white_list_status`: Status result from white list checking (nullable)  
  Examples: "verified", "not_found", "error"

### Relationships
- Belongs to one `Contractor`

### Validation / Business Logic
- `iban` should be validated based on standard IBAN rules
- `currencies` must contain valid ISO 4217 currency codes
- White list checks are optional but recommended for Polish contractors (e.g., check NIP & bank account in KAS white list)

### Notes
- Each contractor may have multiple bank accounts, but typically only one marked as `is_default`
- White list status could be periodically refreshed automatically

---

## [ContractorContactPerson]

Represents a person of contact associated with a contractor, e.g., account managers, billing specialists.

### Key Fields
- `id`: UUID primary key
- `contractor_id`: UUID, references the contractor this person belongs to
- `name`: Full name of the contact person
- `email`: Contact email (nullable)
- `phone`: Contact phone number (nullable)
- `position`: Job title or role within the contractor’s organization (nullable)
- `description`: Additional notes or responsibilities (nullable)

### Relationships
- Belongs to one `Contractor`

### Notes
- Useful for specifying points of contact for specific matters like finance, logistics, or support
- Can store multiple contact persons per contractor for different roles
- No uniqueness constraint on email or phone (can have duplicates if needed)