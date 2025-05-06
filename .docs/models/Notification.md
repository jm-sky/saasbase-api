# Notification Model

> **Note:** This model represents the database storage for Laravel's native notification system. We are using Laravel's built-in notification features and database notifications channel.

Represents a notification that can be sent to users through various channels. Implements Laravel's standard notification system including database storage, email channel, and other notification channels provided by Laravel.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid, nullable) - Reference to the tenant (null for system notifications)
- `type` (varchar) - Notification class name
- `notifiable_type` (varchar) - Type of entity being notified
- `notifiable_id` (uuid) - ID of the entity being notified
- `data` (json) - Notification payload and configuration
- `read_at` (timestamp, nullable) - When the notification was read
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `tenant` - BelongsTo relationship to [Tenant](./Tenant.md)
- `notifiable` - MorphTo relationship to notifiable entity (usually [User](./User.md))

## Business Rules

1. Uses Laravel's native notification system and channels
2. Notifications can be tenant-scoped or system-wide
3. Users can customize their notification preferences:
   - Channel preferences (email, database, SMS, etc.)
   - Frequency (immediate, daily digest, weekly digest)
   - Types of notifications they want to receive
4. Notifications can be marked as read/unread
5. Old notifications may be automatically cleaned up
6. Notifications support Laravel's standard delivery channels:
   - Database (for in-app notifications)
   - Email (using Laravel Mail)
   - Additional channels can be added through Laravel's notification system:
     - SMS (via available Laravel packages)
     - Push notifications (via available Laravel packages)

## Usage

```php
// Using Laravel's native notification system
use Illuminate\Support\Facades\Notification;

// Create and send a notification
Notification::send($user, new TaskAssigned($task));

// Mark notification as read
$notification->markAsRead();

// Get unread notifications for user
$unread = $user->unreadNotifications;

// Get notifications with tenant context
$notifications = Notification::where('tenant_id', $tenant->id)
    ->whereNull('read_at')
    ->get();

// Send to multiple channels
Notification::route('mail', $user->email)
    ->route('database', $user->id)
    ->notify(new PaymentReceived($payment));
```

## Common Notification Types

1. User Management
   - Welcome notification
   - Password reset
   - Email verification
   - Profile updates

2. Project Management
   - Task assignments
   - Due date reminders
   - Project status changes
   - Comment notifications

3. Billing & Subscription
   - Invoice generated
   - Payment received
   - Subscription renewal
   - Trial ending soon

4. System
   - Maintenance notifications
   - Security alerts
   - Feature announcements 
