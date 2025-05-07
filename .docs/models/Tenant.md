# Tenant Model

Represents an organizational entity in the system (e.g., company, team). A tenant owns its data and defines access boundaries.

## Attributes

- `id` (uuid) - Primary key
- `name` (varchar) - Tenant name
- `type` (varchar) - Type of tenant (e.g., 'default', 'office')
- `settings` (json, nullable) - Tenant-specific settings and preferences
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `users` - BelongsToMany relationship to [User](./User.md) through tenant_users pivot
- `orgUnits` - HasMany relationship to [OrganizationUnit](./OrganizationUnit.md)
- `projects` - HasMany relationship to [Project](./Project.md)
- `contractors` - HasMany relationship to [Contractor](./Contractor.md)
- `employees` - HasMany relationship to [Employee](./Employee.md)
- `products` - HasMany relationship to [Product](./Product.md)
- `invoices` - HasMany relationship to [Invoice](./Invoice.md)
- `bankAccounts` - MorphMany relationship to [BankAccount](./BankAccount.md)
- `logo` - MorphOne relationship to [Media](./Media.md) (using Spatie Media Library)
- `documents` - MorphMany relationship to [Media](./Media.md) (using Spatie Media Library)
- `addresses` - HasMany relationship to [Address](./Address.md) (polymorphic)
- `officeRelations` - HasMany relationship to [OfficeTenantRelation](./OfficeTenantRelation.md)

## Business Rules

1. Data Scope:
   - All main entities are scoped by `tenant_id`
   - Each tenant has its own isolated data set
   - Cross-tenant access is controlled via explicit relationships

2. Tenant Types:
   - 'default' - Standard tenant organization
   - 'office' - Special tenant type for supervising offices
   - Type determines available features and relationships

3. Access Control:
   - Has its own roles and permissions setup
   - Users can belong to multiple tenants
   - Each tenant manages its own user access rights

4. Office Relations:
   - Can be serviced by Office-type tenants
   - Office relationships track service agreements and terms

## Usage

The Tenant model is used to:
- Define organizational boundaries
- Manage multi-tenant data isolation
- Control user access and permissions
- Organize business entities and resources
- Track office service relationships
- Store organization-wide settings
