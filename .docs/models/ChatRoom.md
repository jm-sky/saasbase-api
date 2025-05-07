# ChatRoom Model

Represents a chat space where users can exchange messages. Supports different types of rooms: direct messages, group chats, and channels.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid) - Reference to the tenant
- `name` (varchar) - Room name (auto-generated for direct messages)
- `type` (varchar) - Room type ('direct', 'group', 'channel')
- `description` (text, nullable) - Room description
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `tenant` - BelongsTo relationship to [Tenant](./Tenant.md)
- `participants` - HasMany relationship to [ChatParticipant](./ChatParticipant.md)
- `messages` - HasMany relationship to [ChatMessage](./ChatMessage.md)
- `users` - BelongsToMany relationship to [User](./User.md) through [ChatParticipant](./ChatParticipant.md)

## Business Rules

1. Room Types:
   - Direct: Private 1-1 chat between two users
   - Group: Private group chat with selected participants
   - Channel: Public or private channel that users can join

2. Access Control:
   - Direct rooms are always private
   - Group rooms are invitation-only
   - Channels can be public (anyone can join) or private (invitation required)

3. Naming:
   - Direct chat names are auto-generated from participant names
   - Group chats and channels require explicit names
   - Names must be unique within a tenant

4. Participant Management:
   - Direct chats always have exactly 2 participants
   - Groups and channels can have multiple participants
   - Participants can have different roles (admin, moderator, member)

## Usage

```php
// Create a direct message room
$room = ChatRoom::createDirect($user1, $user2);

// Create a group chat
$room = ChatRoom::create([
    'tenant_id' => $tenant->id,
    'name' => 'Project Team',
    'type' => 'group',
    'description' => 'Project coordination'
]);

// Add participants to a group
$room->addParticipants($users, 'member');

// Get user's chat rooms
$rooms = ChatRoom::forUser($user)
    ->with(['lastMessage', 'participants'])
    ->get();

// Get room messages
$messages = $room->messages()
    ->with('user')
    ->latest()
    ->paginate(50);
``` 
