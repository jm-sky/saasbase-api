# ActionLog Model

Represents an audit trail of actions performed by users in the system.

## Attributes

- `id` (uuid) - Primary key
- `user_id` (uuid) - Reference to the user who performed the action
- `action_type` (varchar) - Type of action performed (e.g., 'create', 'update', 'delete')
- `description` (text, nullable) - Detailed description of the action
- `model_type` (varchar) - The type of model affected (e.g., 'User', 'Project')
- `model_id` (uuid) - ID of the affected model
- `ip_address` (varchar, nullable) - IP address from which the action was performed
- `created_at` (timestamp) - When the action occurred

## Relationships

- `user` - BelongsTo relationship to User
- `model` - MorphTo relationship to the affected model

## Usage

The ActionLog model is used to:
- Track all significant changes in the system
- Provide audit trails for compliance
- Monitor user activity
- Support debugging and issue investigation
- Enable activity history views

## Business Rules

1. All create/update/delete operations on major entities should be logged
2. Sensitive data should be masked in the logs
3. Logs should be immutable (no updates or deletes)
4. IP addresses should be stored in compliance with privacy laws
5. Logs should be retained according to compliance requirements

## API Endpoints

- `GET /api/action-logs` - List action logs (admin only)
- `GET /api/action-logs/{id}` - Get specific log details
- `GET /api/{model}/{id}/action-logs` - Get logs for specific entity
- `GET /api/users/{id}/action-logs` - Get logs for specific user 
