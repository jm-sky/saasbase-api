# TimeEntry Model

Represents time spent by users on projects and tasks, supporting both time tracking and billing.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid) - Reference to the tenant
- `user_id` (uuid) - Reference to the user who logged the time
- `project_id` (uuid, nullable) - Reference to the project
- `task_id` (uuid, nullable) - Reference to the specific task
- `description` (text, nullable) - Description of work performed
- `started_at` (timestamp) - When the time entry started
- `ended_at` (timestamp, nullable) - When the time entry ended (null for running entries)
- `duration` (integer) - Duration in minutes
- `is_billable` (boolean) - Whether this time can be billed
- `hourly_rate` (decimal, nullable) - Rate for billing calculations
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `tenant` - BelongsTo relationship to [Tenant](./Tenant.md)
- `user` - BelongsTo relationship to [User](./User.md)
- `project` - BelongsTo relationship to [Project](./Project.md)
- `task` - BelongsTo relationship to [Task](./Task.md)

## Business Rules

1. Time entries must have either a project_id or task_id (or both)
2. Duration is automatically calculated from started_at and ended_at
3. Running time entries (no ended_at) have their duration updated periodically
4. Billable entries must have an hourly_rate set
5. Users can only view/edit their own time entries unless they have special permissions
6. Time entries can be exported for billing and reporting

## Usage

```php
// Start a new time entry
$entry = TimeEntry::create([
    'user_id' => Auth::id(),
    'project_id' => $project->id,
    'description' => 'Working on feature X',
    'started_at' => now(),
    'is_billable' => true,
    'hourly_rate' => $project->billing_rate
]);

// Stop time tracking
$entry->update([
    'ended_at' => now()
]);

// Get billable hours for a project
$billableMinutes = TimeEntry::where('project_id', $project->id)
    ->where('is_billable', true)
    ->sum('duration');
``` 
