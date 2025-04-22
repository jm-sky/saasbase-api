## [Permission](./Permission.md)

Represents a single atomic permission that can be assigned to a role.

### Key Fields
- `id`: UUID
- `key`: Unique string identifier, e.g. `"create_invoice"`, `"view_project"`

### Notes
- Permissions are assigned to roles
- Core permissions are seeded into the system; tenants can manage only assignments
