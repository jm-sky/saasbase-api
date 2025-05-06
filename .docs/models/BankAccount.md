# BankAccount Model

Represents a bank account that can be associated with various entities (contractors, users, tenants) through a polymorphic relationship.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid, nullable) - Reference to the tenant (null for user-specific accounts)
- `bankable_id` (uuid) - ID of the related entity (contractor, user, or tenant)
- `bankable_type` (varchar) - Type of the related entity ('contractors', 'users', 'tenants')
- `name` (varchar) - Account name/label
- `bank_name` (varchar) - Name of the bank
- `account_number` (varchar) - Full bank account number (IBAN format)
- `swift` (varchar, nullable) - SWIFT/BIC code
- `is_default` (boolean) - Whether this is the default account for the entity
- `currency` (varchar) - Account currency code (ISO 4217)
- `description` (text, nullable) - Additional notes
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `tenant` - BelongsTo relationship to [Tenant](./Tenant.md)
- `bankable` - MorphTo relationship to:
  - [Contractor](./Contractor.md)
  - [User](./User.md)
  - [Tenant](./Tenant.md)

## Business Rules

1. Account Number Validation:
   - Must be a valid IBAN format
   - Validated according to country-specific rules
   - Uniqueness checked within the same bankable entity

2. Default Account:
   - Only one account can be marked as default per entity
   - Setting a new default automatically unsets previous default
   - Default accounts cannot be deleted while they are default

3. Tenant Scoping:
   - Contractor accounts are always tenant-scoped
   - User accounts can be tenant-independent
   - Tenant accounts reference themselves in tenant_id

4. Currency:
   - Must be a valid ISO 4217 currency code
   - Used for payment processing and reporting
   - Affects available payment methods

## Usage

The BankAccount model is used to:
- Store bank account details for various entities
- Process payments and transfers
- Generate financial documents
- Manage default payment methods
- Support multi-currency operations
