# Comment Model

Represents a user comment that can be attached to various entities in the system through polymorphic relationships. Supports Markdown formatting for rich text content.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid) - Reference to the tenant
- `user_id` (uuid) - Reference to the commenting user
- `parent_id` (uuid, nullable) - Reference to parent comment for threaded discussions
- `content` (text) - Comment text content in Markdown format
- `commentable_type` (varchar) - Type of entity being commented on
- `commentable_id` (uuid) - ID of the commented entity
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp (tracks edits)

## Relationships

- `tenant` - BelongsTo relationship to Tenant
- `user` - BelongsTo relationship to User
- `commentable` - MorphTo relationship to the commented entity
- `parent` - BelongsTo relationship to parent Comment
- `replies` - HasMany relationship to child Comments
- `media` - MorphMany relationship to Media (using Spatie Media Library)
- `mentions` - HasMany relationship to UserMention

## Usage

The Comment model is used to:
- Enable discussion on various entities with Markdown formatting
- Support threaded conversations through parent-child relationships
- Track comment edits via updated_at timestamp
- Allow file attachments via Spatie Media Library
- Enable user mentions/notifications
- Facilitate team communication

## Business Rules

1. Comments must have non-empty content
2. Content is stored and rendered as Markdown
3. Comment deletion should cascade to replies
4. User mentions should trigger notifications
5. Comments should respect entity visibility rules
6. Edits are tracked through the updated_at timestamp
7. Media attachments should be validated
8. Parent comments must exist when specified

## API Endpoints

- `GET /api/{entity}/{id}/comments` - List comments for entity
- `POST /api/{entity}/{id}/comments` - Create new comment
- `GET /api/comments/{id}` - Get comment details
- `PUT /api/comments/{id}` - Update comment
- `DELETE /api/comments/{id}` - Delete comment
- `POST /api/comments/{id}/reply` - Reply to comment
- `POST /api/comments/{id}/media` - Add media attachment 
