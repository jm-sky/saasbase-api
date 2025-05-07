# UserOAuthAccount Model

Represents a user's linked OAuth provider account (e.g., Google, GitHub, etc.).

## Attributes

- `id` (uuid) - Primary key
- `user_id` (uuid) - Reference to the user
- `provider` (varchar) - OAuth provider name (e.g., 'google', 'github')
- `provider_user_id` (varchar) - Unique ID from the OAuth provider
- `email` (varchar, nullable) - Email associated with the OAuth account
- `linked_at` (timestamp) - When the account was linked

## Relationships

- `user` - BelongsTo relationship to User

## Usage

The UserOAuthAccount model is used to:
- Link external OAuth provider accounts to user accounts
- Enable social login functionality
- Track OAuth account connections
- Verify user emails through OAuth providers

## Business Rules

1. A user can have multiple OAuth accounts
2. Each provider_user_id must be unique per provider
3. OAuth accounts cannot be transferred between users
4. Email verification status from OAuth can be used to verify user email
5. Deleting a user should delete all associated OAuth accounts

## API Endpoints

- `GET /api/user/oauth-accounts` - List user's linked OAuth accounts
- `POST /api/auth/{provider}/link` - Link a new OAuth account
- `DELETE /api/auth/{provider}/unlink` - Unlink an OAuth account
- `GET /api/auth/{provider}/callback` - OAuth provider callback handling 
