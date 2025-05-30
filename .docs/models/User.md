# User Model

Represents an authenticated user in the system. Users can belong to tenants, hold roles, and interact with resources depending on their permissions.

## Attributes

- `id` (uuid) - Primary key
- `first_name` (varchar) - User's given name
- `last_name` (varchar) - User's surname
- `email` (varchar) - User login identifier (used for authentication, must be unique)
- `phone` (varchar, nullable) - Optional phone number
- `password` (varchar) - Encrypted password
- `description` (text, nullable) - Optional bio or user info
- `birth_date` (date, nullable) - Optional birth date
- `is_admin` (boolean) - Whether the user has global admin privileges
- `email_confirmed_at` (timestamp, nullable) - Timestamp when email was confirmed
- `is_public_profile` (boolean) - Whether the user is visible publicly or only within tenants
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `settings` - HasOne relationship to [UserSettings](./UserSettings.md)
- `addresses` - HasMany relationship to [Address](./Address.md) (polymorphic)
- `bankAccounts` - MorphMany relationship to [BankAccount](./BankAccount.md)
- `oauthAccounts` - HasMany relationship to [UserOAuthAccount](./UserOAuthAccount.md)
- `avatar` - MorphOne relationship to [Media](./Media.md) (using Spatie Media Library)
- `documents` - MorphMany relationship to [Media](./Media.md) (using Spatie Media Library)
- `skills` - BelongsToMany relationship to [Skill](./Skill.md) through user_skill pivot
- `orgUnits` - BelongsToMany relationship to [OrganizationUnit](./OrganizationUnit.md) through [OrgUnitUser](./OrgUnitUser.md)
- `comments` - HasMany relationship to [Comment](./Comment.md)

## Business Rules

1. Authentication:
   - JWT-based authentication
   - Login via email and password
   - Optional email and phone verification flows
   - 2FA support (TOTP, SMS or app-based in future)

2. Settings & Preferences:
   - Theme preferences (light/dark)
   - Language settings (e.g., "en", "pl")
   - Time zone (e.g., "Europe/Warsaw")
   - Number formatting (decimal separator)

3. Profile Management:
   - Public/private profile visibility
   - Skill management with optional proficiency levels
   - Document and avatar upload support

4. Security:
   - Passwords are always encrypted
   - Email must be unique across the system
   - Admin privileges give system-wide access

## Usage

The User model is central to:
- Authentication and authorization
- Profile management
- Tenant membership and roles
- Skill tracking and professional development
- Document management
- System-wide activity tracking
