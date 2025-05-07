# OfficeTenantRelation Model

Represents the relationship between an office agreement and a service type, including specific terms and pricing.

## Attributes

- `id` (uuid) - Primary key
- `office_agreement_id` (uuid) - Reference to the office agreement
- `office_service_type_id` (uuid) - Reference to the service type
- `price` (decimal) - Price for this service
- `price_type` (varchar) - Type of pricing (e.g., 'per_month', 'per_use', 'fixed')
- `service_terms` (text, nullable) - Specific terms for this service
- `start_date` (date) - When the service begins
- `end_date` (date, nullable) - When the service ends (null for indefinite)
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `officeAgreement` - BelongsTo relationship to OfficeAgreement
- `officeServiceType` - BelongsTo relationship to OfficeServiceType

## Usage

The OfficeTenantRelation model is used to:
- Define specific service arrangements within agreements
- Set and track service pricing
- Manage service durations
- Support service-based billing
- Track individual service usage and terms

## Business Rules

1. Service dates must fall within agreement dates
2. Price must be non-negative
3. Price type must be one of predefined types
4. Service can't be added to inactive agreements
5. Changes should be logged for audit purposes
6. Service terms should not conflict with agreement terms

## API Endpoints

- `GET /api/office-agreements/{id}/services` - List services for agreement
- `POST /api/office-agreements/{id}/services` - Add service to agreement
- `GET /api/office-tenant-relations/{id}` - Get service relation details
- `PUT /api/office-tenant-relations/{id}` - Update service relation
- `DELETE /api/office-tenant-relations/{id}` - Remove service from agreement 
