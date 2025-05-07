# UserSettings Model

Represents user-specific configuration and preferences in the system.

## Attributes

- `user_id` (uuid) - Primary key and foreign key to User
- `language` (varchar, nullable) - User's preferred interface language
- `theme` (varchar, nullable) - User's preferred UI theme
- `timezone` (varchar, nullable) - User's timezone for date/time display
- `two_factor_enabled` (boolean) - Whether 2FA is enabled for the user
- `two_factor_confirmed` (boolean) - Whether 2FA setup has been confirmed
- `preferences` (text, nullable) - JSON-encoded user preferences

## Relationships

- `user` - BelongsTo relationship to User

## Usage

The UserSettings model is used to:
- Store user-specific application preferences
- Manage two-factor authentication settings
- Configure localization preferences (language, timezone)
- Customize UI appearance

## Business Rules

1. Each user can have only one settings record
2. Settings are created automatically when a user is created
3. Language must be a valid ISO language code if set
4. Timezone must be a valid timezone identifier if set
5. Preferences must be valid JSON if set
6. Two-factor authentication must be confirmed before being considered enabled

## API Endpoints

- `GET /api/user/settings` - Get current user's settings
- `PATCH /api/user/settings` - Update user settings
- `POST /api/user/settings/two-factor` - Enable/configure 2FA
- `DELETE /api/user/settings/two-factor` - Disable 2FA 
