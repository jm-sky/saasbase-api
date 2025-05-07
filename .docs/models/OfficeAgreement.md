# OfficeAgreement Model

Represents a contractual agreement between a tenant and an office, defining the terms and duration of office space usage.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid) - Reference to the tenant
- `office_id` (uuid) - Reference to the office
- `agreement_start_date` (date) - When the agreement begins
- `agreement_end_date` (date, nullable) - When the agreement ends (null for indefinite)
- `contract_terms` (text, nullable) - Detailed terms and conditions
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `tenant` - BelongsTo relationship to Tenant
- `office` - BelongsTo relationship to Office
- `officeServiceTypes` - BelongsToMany relationship to OfficeServiceType through office_tenant_relations
- `relations` - HasMany relationship to OfficeTenantRelation

## Usage

The OfficeAgreement model is used to:
- Define office space rental agreements
- Track agreement durations and terms
- Manage office-tenant relationships
- Support billing and invoicing
- Enable office space management

## Business Rules

1. An office can have multiple agreements with different tenants
2. Agreement dates must not overlap for the same office-tenant pair
3. Start date must be before end date (if end date is specified)
4. Agreement must be linked to active office and tenant
5. Agreement changes should be logged for audit purposes
6. Agreement termination should trigger related service termination

## API Endpoints

- `GET /api/office-agreements` - List agreements
- `POST /api/office-agreements` - Create new agreement
- `GET /api/office-agreements/{id}` - Get agreement details
- `PUT /api/office-agreements/{id}` - Update agreement
- `DELETE /api/office-agreements/{id}` - Terminate agreement
- `GET /api/offices/{id}/agreements` - List office's agreements
- `GET /api/tenants/{id}/office-agreements` - List tenant's agreements 
