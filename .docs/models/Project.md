# Project Model

Represents a business or development initiative with JIRA-like project management capabilities.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid) - Reference to the tenant
- `name` (varchar) - Project name
- `description` (text, nullable) - Detailed project description
- `key` (varchar) - Unique project identifier (e.g., 'PROJ', 'HR')
- `status` (varchar) - Current project status
- `start_date` (date, nullable) - Project start date
- `end_date` (date, nullable) - Project end date
- `is_active` (boolean) - Whether the project is currently active
- `settings` (json, nullable) - Project-specific settings
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `tenant` - BelongsTo relationship to Tenant
- `tasks` - HasMany relationship to Task
- `sprints` - HasMany relationship to Sprint
- `members` - BelongsToMany relationship to User through project_members
- `tags` - MorphToMany relationship to Tag
- `comments` - MorphMany relationship to Comment
- `attachments` - MorphMany relationship to Attachment
- `timeEntries` - HasMany relationship to TimeEntry

## Usage

The Project model is used to:
- Organize and group related tasks
- Track project progress and status
- Manage project team members
- Support agile methodologies through sprints
- Enable time tracking and reporting
- Store project-specific configurations

## Business Rules

1. Project key must be unique within a tenant
2. Project must have at least one member (owner)
3. End date must be after start date if both are specified
4. Project status changes should be logged
5. Project deletion should handle related entities
6. Settings should follow defined schema
7. Active projects must have valid dates

## API Endpoints

- `GET /api/projects` - List projects
- `POST /api/projects` - Create new project
- `GET /api/projects/{id}` - Get project details
- `PUT /api/projects/{id}` - Update project
- `DELETE /api/projects/{id}` - Archive/delete project
- `GET /api/projects/{id}/tasks` - List project tasks
- `GET /api/projects/{id}/members` - List project members
- `GET /api/projects/{id}/activity` - Get project activity feed 
