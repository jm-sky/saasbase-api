## [User]

Represents an authenticated user in the system. Users can belong to tenants, hold roles, and interact with resources depending on their permissions.

### Key Fields
- `id`: UUID primary key
- `first_name`: User's given name
- `last_name`: User's surname
- `email`: User login identifier (used for authentication, must be unique)
- `phone`: Optional phone number
- `password`: Encrypted password
- `description`: Optional bio or user info
- `birth_date`: Optional birth date
- `is_admin`: Whether the user has global admin privileges
- `email_confirmed_at`: Timestamp when email was confirmed
- `created_at`: Timestamp of user creation
- `updated_at`: Timestamp of last update

### Authentication
- JWT-based authentication
- Login via email and password
- Optional email and phone verification flows
- 2FA support (TOTP, SMS or app-based in future)

### Preferences
Stored in the `settings` key-value structure:
- `theme`: e.g. "light" | "dark"
- `language`: e.g. "en", "pl"
- `time_zone`: e.g. "Europe/Warsaw"
- `decimal_separator`: e.g. "," or "."

### Profile Visibility
- `is_public_profile`: Whether the user is visible publicly or only within tenants

### Relationships
- Belongs to many tenants via memberships (e.g., roles, team membership)
- Linked to employees (optional)
- Can be authenticated via OAuth providers
- Can have assigned skills (many-to-many)

### Notes
- Skills are stored in a separate `skills` dictionary
- User skills are managed through a pivot table (`user_skills`) with optional levels