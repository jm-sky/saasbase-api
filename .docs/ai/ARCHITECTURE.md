# Project Architecture Guidelines for AI

## Core Technologies
- Laravel 12.x
- PHP 8.2+
- PostgreSQL 17
- Redis for caching
- MinIO for S3-compatible storage
- Soketi for WebSockets
- Mailpit for local email testing

## Required Packages
- Laravel Horizon for queue monitoring
- Laravel Telescope for debugging
- JWT Authentication (tymon/jwt-auth)
- UUID for all models

## Database Conventions
1. All tables MUST use UUID as primary key
2. Timestamps (created_at, updated_at) are required on all tables
3. Soft deletes (deleted_at) should be used unless explicitly not needed
4. Foreign keys should be named as `{table_singular}_id`

## Model Conventions
```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BaseModel extends Model
{
    use HasUuids;
    
    protected $keyType = 'string';
    public $incrementing = false;
}
```

## Authentication
- JWT-based authentication
- Tokens should expire after 1 hour
- Refresh tokens should expire after 2 weeks
- Multi-device support enabled

## API Standards
1. RESTful endpoints
2. API versioning in URL (e.g., /api/v1/)
3. JSON:API specification for responses
4. Rate limiting enabled by default