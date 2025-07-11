# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Testing and Quality Assurance
```bash
# Run all tests
./vendor/bin/sail artisan test

# Run PHPUnit directly
./vendor/bin/sail vendor/bin/phpunit

# Run specific test file
./vendor/bin/sail artisan test tests/Feature/Auth/UserSettingsTest.php

# Run static analysis with PHPStan
./vendor/bin/sail vendor/bin/phpstan analyse --memory-limit=2G
# Or use composer shortcut
./vendor/bin/sail composer larastan

# Code style check (dry run)
./vendor/bin/sail composer cs

# Code style fix
./vendor/bin/sail composer csf
```

### Development Server
```bash
# Start development server with queue and logs
composer run dev

# Individual components
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
```

### Database Management
```bash
# Run migrations
./vendor/bin/sail artisan migrate

# Run seeders
./vendor/bin/sail artisan db:seed

# Fresh database with seeders
./vendor/bin/sail artisan migrate:fresh --seed
```

### Code Generation
```bash
# Generate complete domain model with all scaffolding
./vendor/bin/sail artisan make:domain-model {ModelName} {DomainName}

# Example: Create Invoice model in Billing domain
./vendor/bin/sail artisan make:domain-model Invoice Billing
```

## Architecture Overview

This is a **multi-tenant SaaS application** built with Laravel using **Domain-Driven Design (DDD)** principles. The codebase is organized into 25+ business domains, each containing a complete set of components following hexagonal architecture.

### Domain Structure

Each domain in `app/Domain/` follows this consistent pattern:
- **Actions/** - Command objects for complex business operations
- **Controllers/** - API endpoints with standardized filtering
- **DTOs/** - Type-safe data transfer objects
- **Enums/** - PHP 8.1+ enums for type safety
- **Models/** - Eloquent models with tenant scoping
- **Requests/** - Form request validation
- **Resources/** - API response transformers
- **Services/** - Domain business logic
- **Traits/** - Reusable behaviors

### Key Architectural Patterns

#### Multi-Tenancy
- All models use tenant scoping via `TenantScope`
- Global scope `GlobalOrCurrentTenantScope` automatically filters data
- Bypass mechanism: `Tenant::$BYPASSED_TENANT_ID` for system operations (`Tenant::bypassTenant(string $tenantId, Closure)`)
- Traits: `IsGlobalOrBelongsToTenant`, `BelongsToTenant`

#### Base Classes
- **BaseModel**: All models extend this with ULID support and factory integration
- **BaseDataDTO**: Abstract base for DTOs with type-safe transformations
- **BaseFormRequest**: Standardized form request validation (transforming camelCase to snakeCase in `validated()` method)

#### Standardized Behaviors
- **HasIndexQuery**: Consistent controller filtering and pagination (using Spatie Query Builder)
- **HasActivityLogging**: Automatic audit trail generation
- **HasMediaSignedUrls**: Secure file access patterns

### Major Domains

**Core Business:**
- **Auth** - Authentication, JWT, user management
- **Tenant** - Multi-tenancy, organization units
- **Invoice** - Invoice generation, templates, PDF export
- **Expense** - Expense tracking, OCR processing, approval workflows
- **Financial** - VAT rates, exchange rates, payment methods
- **Contractors** - Contractor management, registry confirmation
- **Products** - Product catalog with measurement units
- **Projects** - Project management with tasks and statuses

**Supporting Systems:**
- **Approval** - Flexible workflow engine with organizational hierarchy
- **Template** - Multi-language invoice templates
- **Subscription** - Stripe integration, billing management
- **Common** - Shared utilities, base classes, media handling
- **AI** - AI chat integration with OpenRouter

### Important Development Patterns

#### Use the Domain Model Generator
Always use the custom artisan command for new domain models:
```bash
php artisan make:domain-model {ModelName} {DomainName}
```
This generates: Model, DTO, Controller, Resource, and Request classes with proper structure.

#### Tenant Scoping
All tenant-scoped models automatically filter by current tenant. Use `Tenant::$BYPASSED_TENANT_ID` for system operations that need cross-tenant access.

#### Action Pattern
Complex business operations use dedicated Action classes:
```php
// Example: AllocateExpenseAction, ProcessApprovalDecisionAction
$action = new AllocateExpenseAction();
$result = $action->execute($expense, $allocationData);
```

#### Interface-Driven Design
Many domains use interfaces for contracts:
- `AllocationDimensionInterface` for expense allocation dimensions
- Each interface includes comprehensive documentation

#### Event-Driven Architecture
Domains communicate via events:
- `UserCreated`, `MessageSent`, `OcrExpenseCompleted`
- Jobs for asynchronous processing
- Notifications for cross-domain communication

### Testing Strategy

- **Feature Tests**: End-to-end API testing with tenant isolation
- **Unit Tests**: Individual service and action testing
- **Traits**: `WithAuthenticatedUser`, `WithFakeStorage` for test setup
- **Factories**: Automatic factory detection in BaseModel

### Code Style and Quality

- **PHPStan Level 3**: Static analysis enforced
- **PHP-CS-Fixer**: Consistent code formatting
- **Enums**: Extensive use for type safety
- **DTOs**: Strongly typed data transfer

### Docker Environment

The application uses Docker Compose with:
- PostgreSQL 17 (primary database)
- Redis (cache and queues)
- MinIO (S3-compatible storage)
- Mailpit (development SMTP)
- Soketi (WebSocket server)

Start with: `docker-compose up -d` or rather `./scripts/up.sh -d` for development

### Key Configuration Files

- **phpstan.neon**: Static analysis configuration (Level 3)
- **phpunit.xml**: Test configuration with environment variables
- **composer.json**: Scripts for common development tasks
- **.cursor/rules/**: Development guidelines and patterns

### Special Features

- **OCR Integration**: Sophisticated document processing in Expense domain
- **Approval Workflows**: Flexible multi-step approval system
- **Template System**: Multi-language invoice templates with PDF generation
- **Allocation Engine**: Expense allocation across multiple dimensions
- **Activity Logging**: Comprehensive audit trail across all domains

### Invoice Template Development

**Important**: When working with invoice templates, always refer to `/resources/templates/invoice/README.md` which contains comprehensive documentation about:
- mPDF HTML/CSS support and limitations
- Best practices for PDF template development
- Common issues and solutions
- Supported vs unsupported CSS features
- Layout recommendations (use tables, avoid flexbox)

Key points for invoice template development:
- Use table-based layouts instead of flexbox/grid
- Stick to basic CSS properties
- Test in PDF output, not browser preview
- Footer with datetime, app name, and page numbers is automatically added via mPDF
- Templates are located in `resources/templates/invoice/`
- CSS is in `resources/css/invoice-pdf.css`
