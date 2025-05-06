# OrganizationUnit Model

Represents an organizational unit (department, team, division, etc.) within a tenant's organizational structure.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid) - Reference to the tenant this unit belongs to
- `parent_id` (uuid, nullable) - Reference to parent organization unit, null for top-level units
- `name` (string) - Full name of the organizational unit
- `short_name` (string) - Abbreviated or short name for the unit
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `tenant` - BelongsTo relationship to Tenant
- `parent` - BelongsTo relationship to OrganizationUnit (self-referential)
- `children` - HasMany relationship to OrganizationUnit (self-referential)
- `users` - BelongsToMany relationship to User through OrgUnitUser
- `orgUnitUsers` - HasMany relationship to OrgUnitUser

## Usage

The OrganizationUnit model is used to:
- Define hierarchical organizational structure within a tenant
- Group users into teams/departments
- Manage access control based on organizational structure
- Support reporting and resource allocation at organizational unit level

## Business Rules

1. Each unit must belong to a tenant
2. A unit can have only one parent unit
3. Circular references in parent-child relationships are not allowed
4. Short name should be unique within the same tenant
5. Users can belong to multiple organizational units with different roles

## API Endpoints

- `GET /api/organization-units` - List all units (hierarchical)
- `GET /api/organization-units/{id}` - Get specific unit details
- `GET /api/organization-units/{id}/users` - List users in the unit
- `GET /api/organization-units/{id}/children` - List child units 
