# Project Conventions for AI

## Directory Structure
```
app/
├── Domain/
│   ├── Auth/
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   ├── Events/
│   │   ├── Exceptions/
│   │   ├── Models/
│   │   ├── Policies/
│   │   └── Services/
│   ├── Tenancy/
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   ├── Models/
│   │   └── Services/
│   ├── Projects/
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   ├── Events/
│   │   ├── Models/
│   │   ├── Policies/
│   │   └── Services/
│   ├── Contractors/
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   ├── Models/
│   │   └── Services/
│   ├── Invoicing/
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── States/
│   ├── Skills/
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   ├── Models/
│   │   └── Services/
│   └── Common/
│       ├── Actions/
│       ├── DTOs/
│       ├── Models/
│       └── Services/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── Tenancy/
│   │   ├── Projects/
│   │   ├── Contractors/
│   │   ├── Invoicing/
│   │   └── Skills/
│   ├── Middleware/
│   └── Requests/
│       ├── Auth/
│       ├── Tenancy/
│       ├── Projects/
│       ├── Contractors/
│       ├── Invoicing/
│       └── Skills/
├── Support/
│   ├── Traits/
│   └── Helpers/
└── Providers/
```

## Naming Conventions

### Domain Layer
1. Models: Singular, PascalCase (e.g., `User`, `Project`)
2. DTOs: Suffix with DTO, PascalCase (e.g., `CreateUserDTO`, `UpdateProjectDTO`)
3. Actions: Verb + Noun + Action (e.g., `CreateUserAction`, `UpdateProjectAction`)
4. Services: Suffix with Service (e.g., `UserService`, `InvoicingService`)
5. Events: Past tense, PascalCase (e.g., `UserRegistered`, `ProjectCreated`)
6. States: Suffix with State (e.g., `InvoiceDraftState`, `InvoicePaidState`)
7. Policies: Suffix with Policy (e.g., `ProjectPolicy`, `InvoicePolicy`)

### HTTP Layer
1. Controllers: Plural, Suffix with Controller (e.g., `UsersController`, `ProjectsController`)
2. Requests: Verb + Noun + Request (e.g., `CreateUserRequest`, `UpdateProjectRequest`)
3. Resources: Singular, Suffix with Resource (e.g., `UserResource`, `ProjectResource`)

### Database
1. Tables: Plural, snake_case (e.g., `users`, `project_users`)
2. Pivot Tables: Singular model names in alphabetical order (e.g., `project_user`, `skill_user`)
3. Foreign Keys: Singular model + _id (e.g., `user_id`, `project_id`)

## Domain Organization Rules
1. Each domain should be self-contained with its own models, DTOs, actions, and services
2. Cross-domain interactions should go through services
3. Domain events should be used for cross-domain communication
4. Common domain contains shared functionality used across other domains

## Testing Structure
```
tests/
├── Feature/
│   ├── Auth/
│   ├── Tenancy/
│   ├── Projects/
│   ├── Contractors/
│   ├── Invoicing/
│   └── Skills/
└── Unit/
    ├── Domain/
    │   ├── Auth/
    │   ├── Tenancy/
    │   ├── Projects/
    │   ├── Contractors/
    │   ├── Invoicing/
    │   └── Skills/
    └── Support/
```

## Testing Conventions
1. Feature tests for HTTP endpoints
2. Unit tests for Actions and Services
3. Database factories for all models
4. Use data providers for edge cases
5. One assertion per test when possible
