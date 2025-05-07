# Contractor Model

Represents a company, person, or foundation that the tenant collaborates with — e.g., clients, suppliers, subcontractors.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid) - Reference to the tenant that owns this contractor
- `name` (varchar) - Full legal or personal name of the contractor
- `type` (varchar) - Nature of the contractor ('company', 'person', 'foundation')
- `tax_id` (varchar, nullable) - Tax identification number (NIP for Polish contractors)
- `email` (varchar, nullable) - Contact email
- `phone` (varchar, nullable) - Contact phone number
- `description` (text, nullable) - Free-form notes
- `preferences` (json, nullable) - Contractor-specific settings (e.g., payment terms, preferred currency)
- `is_supplier` (boolean) - Whether this contractor supplies products/services
- `is_buyer` (boolean) - Whether this contractor purchases products/services
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp
- `deleted_at` (timestamp, nullable) - Soft delete timestamp

## Relationships

- `tenant` - BelongsTo relationship to [Tenant](./Tenant.md)
- `addresses` - HasMany relationship to [Address](./Address.md) (polymorphic)
- `contacts` - HasMany relationship to [ContractorContactPerson](./ContractorContactPerson.md)
- `bankAccounts` - MorphMany relationship to [BankAccount](./BankAccount.md)
- `tags` - BelongsToMany relationship to [Tag](./Tag.md)
- `logo` - MorphOne relationship to [Media](./Media.md) (using Spatie Media Library)
- `documents` - MorphMany relationship to [Media](./Media.md) (using Spatie Media Library)

## Business Rules

1. Tax ID validation:
   - For Polish contractors (country_code="PL"): must be a valid NIP
   - For others: any valid local tax ID format
2. Can be marked as both supplier and buyer if applicable
3. Type determines contextual behavior (e.g., different document requirements)
4. Supports soft deletion via deleted_at
5. Company info can be loaded from external services based on tax_id:
   - For Poland: GUS, REGON, VAT Payers White List
   - For other countries: respective business registries

## Usage

The Contractor model is used to:
- Manage business relationships with clients and suppliers
- Support invoicing and financial operations
- Track contractor details and preferences
- Enable document management and compliance
- Categorize contractors using tags
- Maintain contact information and bank details
