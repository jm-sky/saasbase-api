# Address Model

Represents a physical address that can be associated with various entities (users, contractors, tenants) through a polymorphic relationship.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid, nullable) - Reference to the tenant (null for user addresses)
- `addressable_id` (uuid) - ID of the related entity (user, contractor, or tenant)
- `addressable_type` (varchar) - Type of the related entity ('users', 'contractors', 'tenants')
- `type` (varchar) - Address type ('residence', 'billing', 'correspondence')
- `description` (varchar, nullable) - Optional description/label for the address
- `country_code` (varchar) - ISO country code
- `city` (varchar) - City name
- `postal_code` (varchar, nullable) - Postal/ZIP code
- `street` (varchar, nullable) - Street name
- `building` (varchar, nullable) - Building number/name
- `flat` (varchar, nullable) - Apartment/flat number
- `is_default` (boolean) - Whether this is the default address for the entity
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `tenant` - BelongsTo relationship to Tenant (only for contractor and tenant addresses)
- `addressable` - MorphTo relationship to User/Contractor/Tenant

## Usage

The Address model is used to:
- Store addresses for multiple entity types (polymorphic)
- Manage different address types (residence, billing, correspondence)
- Support default address selection per entity and type
- Provide standardized address formatting across the system

## Business Rules

1. Each entity can have multiple addresses
2. Only one address can be marked as default per entity and type
3. Country code must be a valid ISO code
4. Required fields: country_code, city
5. First address added for a type is automatically set as default
6. When default address is deleted, another should be marked as default if exists
7. Tenant scoping rules:
   - User addresses are NOT tenant-scoped (tenant_id is null)
   - Contractor and tenant addresses ARE tenant-scoped
   - Access control should respect this distinction

## API Endpoints

- `GET /api/{entity}/{id}/addresses` - List entity's addresses
- `POST /api/{entity}/{id}/addresses` - Add new address
- `GET /api/{entity}/{id}/addresses/{addressId}` - Get specific address
- `PUT /api/{entity}/{id}/addresses/{addressId}` - Update address
- `DELETE /api/{entity}/{id}/addresses/{addressId}` - Delete address
- `POST /api/{entity}/{id}/addresses/{addressId}/default` - Set as default address

Where `{entity}` can be: `users`, `contractors`, or `tenants` 
