# Sprint Model

Represents an agile sprint period for organizing and tracking tasks within a project.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid) - Reference to the tenant
- `project_id` (uuid) - Reference to the project
- `name` (varchar) - Sprint name
- `goal` (text, nullable) - Sprint goal description
- `start_date` (date) - Sprint start date
- `end_date` (date) - Sprint end date
- `status` (varchar) - Current sprint status (e.g., 'planned', 'active', 'completed')
- `order` (integer) - Sprint order within project
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `tenant` - BelongsTo relationship to Tenant
- `project` - BelongsTo relationship to Project
- `tasks` - HasMany relationship to Task
- `comments` - MorphMany relationship to Comment
- `attachments` - MorphMany relationship to Attachment

## Usage

The Sprint model is used to:
- Organize tasks into time-boxed iterations
- Track sprint progress and goals
- Support agile/scrum methodologies
- Enable sprint planning and review
- Generate sprint reports and metrics
- Facilitate team velocity tracking

## Business Rules

1. Sprint dates must be within project timeline
2. End date must be after start date
3. Active sprint cannot overlap with other active sprints in same project
4. Sprint status changes should be logged
5. Sprint completion should update task statuses
6. Sprint order must be unique within project
7. Sprint dates should align with project schedule

## API Endpoints

- `GET /api/sprints` - List sprints
- `POST /api/sprints` - Create new sprint
- `GET /api/sprints/{id}` - Get sprint details
- `PUT /api/sprints/{id}` - Update sprint
- `DELETE /api/sprints/{id}` - Delete sprint
- `GET /api/sprints/{id}/tasks` - List sprint tasks
- `GET /api/sprints/{id}/burndown` - Get burndown chart data
- `GET /api/projects/{id}/sprints` - List project sprints
- `POST /api/sprints/{id}/complete` - Complete sprint 
