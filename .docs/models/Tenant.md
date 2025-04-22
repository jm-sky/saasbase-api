## [Tenant](./Tenant.md)

Represents an organizational entity in the system (e.g., company, team). A tenant owns its data and defines access boundaries.

### Key Fields
- `id`: UUID primary key
- `name`: Tenant name
- `type`: e.g., `"default"`, `"office"` (used to distinguish special tenants like supervising offices)
- `logo`: Optional logo (media file)

### Behavior
- All main entities are scoped by `tenant_id`
- Tenants can be serviced by Office tenants (see Office section)

### Relationships
- Has many users (via memberships)
- Owns projects, tasks, invoices, products, employees, etc.
- Has its own roles and permissions setup

---

## [OAuthProvider](./OAuthProvider.md)

Stores external authentication credentials for a user.

### Key Fields
- `id`: UUID
- `user_id`: References `users.id`
- `provider`: e.g. `"google"`, `"github"`
- `provider_user_id`: Remote user ID from the provider
- `access_token`, `refresh_token`: OAuth credentials
- `created_at`, `updated_at`: Timestamps

### Notes
- User can link multiple providers
- Used during login/registration flows