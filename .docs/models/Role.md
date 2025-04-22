## [Role](./Role.md)

Defines a named group of permissions for a tenant.

### Key Fields
- `id`: UUID
- `tenant_id`: Belongs to a tenant
- `name`: Role name (e.g., `"admin"`, `"manager"`, `"viewer"`)

### Relationships
- Many-to-many with users
- Many-to-many with permissions
