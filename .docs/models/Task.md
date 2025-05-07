# Task Model

Represents a unit of work within a project, supporting task management with priorities, statuses, and dependencies.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid) - Reference to the tenant
- `project_id` (uuid) - Reference to the project
- `sprint_id` (uuid, nullable) - Reference to the sprint
- `title` (varchar) - Task title
- `description` (text, nullable) - Detailed task description
- `status` (varchar) - Current task status
- `priority` (varchar) - Task priority level
- `assigned_to` (uuid, nullable) - Reference to assigned user
- `due_date` (date, nullable) - Task due date
- `estimated_hours` (decimal, nullable) - Estimated hours to complete
- `order` (integer) - Task order within project/sprint
- `parent_id` (uuid, nullable) - Reference to parent task
- `created_by` (uuid) - Reference to user who created the task
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `tenant` - BelongsTo relationship to Tenant
- `project` - BelongsTo relationship to Project
- `sprint` - BelongsTo relationship to Sprint
- `assignee` - BelongsTo relationship to User
- `creator` - BelongsTo relationship to User
- `parent` - BelongsTo relationship to Task
- `subtasks` - HasMany relationship to Task
- `dependencies` - BelongsToMany relationship to Task through task_dependencies
- `dependents` - BelongsToMany relationship to Task through task_dependencies
- `tags` - MorphToMany relationship to Tag
- `comments` - MorphMany relationship to Comment
- `attachments` - MorphMany relationship to Attachment
- `timeEntries` - HasMany relationship to TimeEntry

## Usage

The Task model is used to:
- Track individual work items
- Manage task dependencies
- Support task hierarchies (subtasks)
- Enable time tracking
- Facilitate team collaboration
- Monitor project progress

## Business Rules

1. Task must belong to an active project
2. Status transitions should follow defined workflow
3. Dependencies cannot form cycles
4. Subtasks inherit certain properties from parent
5. Due date must be within project timeline
6. Status changes should be logged
7. Time entries should be validated against estimates
8. Task deletion should handle dependencies

## API Endpoints

- `GET /api/tasks` - List tasks
- `POST /api/tasks` - Create new task
- `GET /api/tasks/{id}` - Get task details
- `PUT /api/tasks/{id}` - Update task
- `DELETE /api/tasks/{id}` - Delete task
- `GET /api/tasks/{id}/subtasks` - List subtasks
- `GET /api/tasks/{id}/dependencies` - List dependencies
- `GET /api/tasks/{id}/time-entries` - List time entries
- `POST /api/tasks/{id}/comments` - Add comment
- `POST /api/tasks/{id}/attachments` - Add attachment 
