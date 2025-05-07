# OrgUnitUser Model

Represents the relationship between users and organizational units, including their role within the unit.

## Attributes

- `id` (uuid) - Primary key
- `organization_unit_id` (uuid) - Reference to the organizational unit
- `user_id` (uuid) - Reference to the user
- `role` (string) - User's role within the organizational unit
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `organizationUnit` - BelongsTo relationship to OrganizationUnit
- `user` - BelongsTo relationship to User

## Usage

The OrgUnitUser model is used to:
- Define user membership in organizational units
- Specify user roles within each unit
- Support access control based on organizational structure
- Enable role-based permissions at the organizational unit level

## Business Rules

1. A user can have only one role per organizational unit
2. Valid roles are defined in the application configuration
3. Role changes should be logged for audit purposes
4. Users can belong to multiple organizational units
5. Role must be provided when creating the relationship

## API Endpoints

- `GET /api/users/{id}/organization-units` - List user's organizational units with roles
- `POST /api/organization-units/{id}/users` - Add user to organizational unit
- `PATCH /api/organization-units/{id}/users/{userId}` - Update user's role in unit
- `DELETE /api/organization-units/{id}/users/{userId}` - Remove user from unit 
