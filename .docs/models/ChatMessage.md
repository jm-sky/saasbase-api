# ChatMessage Model

Represents a message sent in a chat room. Supports Markdown formatting and threaded discussions.

## Attributes

- `id` (uuid) - Primary key
- `chat_room_id` (uuid) - Reference to the chat room
- `user_id` (uuid) - Reference to the message author
- `parent_id` (uuid, nullable) - Reference to parent message for threads
- `content` (text) - Message content in Markdown format
- `edited_at` (timestamp, nullable) - When the message was last edited
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `room` - BelongsTo relationship to [ChatRoom](./ChatRoom.md)
- `user` - BelongsTo relationship to [User](./User.md)
- `parent` - BelongsTo relationship to [ChatMessage](./ChatMessage.md)
- `replies` - HasMany relationship to [ChatMessage](./ChatMessage.md)
- `attachments` - MorphMany relationship to [Media](./Media.md) (using Spatie Media Library)

## Business Rules

1. Message Content:
   - Supports Markdown formatting
   - Can include @mentions
   - Can include emoji
   - Can have file attachments

2. Threading:
   - Messages can be replies to other messages
   - Thread depth is limited to one level (replies can't have replies)
   - Parent messages show reply count

3. Editing:
   - Messages can be edited by their authors
   - Edit history is tracked via updated_at
   - Messages marked as edited show edited_at timestamp

4. Permissions:
   - Users can only send messages in rooms they're participants of
   - Message editing limited to author and room admins
   - Message deletion limited to author and room admins

## Usage

```php
// Send a new message
$message = $room->messages()->create([
    'user_id' => Auth::id(),
    'content' => 'Hello team! Check out this **important** update.'
]);

// Reply to a message
$reply = $message->replies()->create([
    'user_id' => Auth::id(),
    'content' => 'Thanks for the update!'
]);

// Edit a message
$message->update([
    'content' => 'Updated content',
    'edited_at' => now()
]);

// Get thread messages
$thread = ChatMessage::where('parent_id', $message->id)
    ->with('user')
    ->latest()
    ->get();

// Add attachment
$message->addMedia($file)
    ->toMediaCollection('attachments');
``` 
