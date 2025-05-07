# OfficeServiceType Model

Represents a type of service that can be provided in an office space (e.g., internet, cleaning, reception).

## Attributes

- `id` (uuid) - Primary key
- `name` (varchar) - Service type name
- `description` (text, nullable) - Detailed description of the service
- `is_active` (boolean) - Whether this service type is currently available
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `officeAgreements` - BelongsToMany relationship to OfficeAgreement through office_tenant_relations
- `relations` - HasMany relationship to OfficeTenantRelation

## Usage

The OfficeServiceType model is used to:
- Define available office services
- Standardize service offerings across offices
- Support service package creation
- Enable service-based billing
- Track service availability

## Business Rules

1. Service types must have unique names
2. Deactivating a service type should not affect existing agreements
3. Service type changes should be logged
4. Service types can be assigned to multiple agreements
5. Service type pricing is defined at the office-tenant relation level

## API Endpoints

- `GET /api/office-service-types` - List service types
- `POST /api/office-service-types` - Create new service type
- `GET /api/office-service-types/{id}` - Get service type details
- `PUT /api/office-service-types/{id}` - Update service type
- `GET /api/office-agreements/{id}/service-types` - List services for agreement 
