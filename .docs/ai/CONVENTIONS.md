# Project Conventions for AI

## Directory Structure
```
app/
├── Actions/ # Single-purpose business logic classes
├── Contracts/ # Interfaces
├── DTOs/ # Data Transfer Objects
├── Events/ # Event classes
├── Exceptions/ # Custom exceptions
├── Http/
│ ├── Controllers/
│ ├── Middleware/
│ ├── Requests/ # Form requests
│ └── Resources/ # API resources
├── Jobs/ # Queue jobs
├── Models/ # Eloquent models
├── Policies/ # Authorization policies
├── Providers/ # Service providers
├── Services/ # Complex business logic
└── Support/ # Helper classes
```

## Naming Conventions
1. Controllers: Plural, PascalCase (e.g., UsersController)
2. Models: Singular, PascalCase (e.g., User)
3. Database: Plural, snake_case (e.g., user_profiles)
4. Actions: Verb + Noun, PascalCase (e.g., CreateUserAction)
5. DTOs: Noun + DTO, PascalCase (e.g., UserDTO)

## Testing Conventions
1. Feature tests for all API endpoints
2. Unit tests for Actions and Services
3. Database factories for all models
4. Use data providers for edge cases
