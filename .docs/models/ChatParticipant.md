# ChatParticipant Model

Represents a user's membership in a chat room, including their role and read status.

## Attributes

- `id` (uuid) - Primary key
- `chat_room_id` (uuid) - Reference to the chat room
- `user_id` (uuid) - Reference to the participant
- `role` (varchar) - Participant's role ('admin', 'moderator', 'member')
- `joined_at` (timestamp) - When the user joined the room
- `last_read_at` (timestamp, nullable) - When the user last read messages
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `room` - BelongsTo relationship to [ChatRoom](./ChatRoom.md)
- `user` - BelongsTo relationship to [User](./User.md)

## Business Rules

1. Roles and Permissions:
   - Admin: Full control (add/remove participants, change settings)
   - Moderator: Can manage messages and participants
   - Member: Can only send/read messages

2. Room Creator:
   - Automatically becomes room admin
   - Last admin cannot leave until transferring admin role

3. Read Status:
   - Tracks when user last viewed messages
   - Used for unread message counts
   - Updated automatically when viewing room

4. Constraints:
   - Users can only be participant once per room
   - Direct messages always have exactly 2 participants
   - Role changes require proper permissions

## Usage

```php
// Add participant to room
$participant = $room->participants()->create([
    'user_id' => $user->id,
    'role' => 'member',
    'joined_at' => now()
]);

// Change participant role
$participant->update([
    'role' => 'moderator'
]);

// Mark messages as read
$participant->update([
    'last_read_at' => now()
]);

// Get unread count
$unreadCount = $room->messages()
    ->where('created_at', '>', $participant->last_read_at)
    ->count();

// Get rooms where user is admin
$adminRooms = ChatRoom::whereHas('participants', function ($query) use ($userId) {
    $query->where('user_id', $userId)
          ->where('role', 'admin');
})->get();
``` 
