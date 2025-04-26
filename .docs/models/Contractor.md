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
