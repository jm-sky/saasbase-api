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