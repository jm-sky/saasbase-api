# Development Tools & Extensions

## Required Tools
1. Laravel Horizon
   - Queue monitoring and management
   - Configure in config/horizon.php

2. Laravel Telescope
   - Development debugging
   - Disable in production
   - Configure in config/telescope.php

3. JWT Authentication
   - Implementation: tymon/jwt-auth
   - Configure in config/jwt.php

## Development Environment
1. Docker (Laravel Sail)
   - PostgreSQL 17
   - Redis
   - MinIO
   - Soketi
   - Mailpit

## Code Quality Tools
1. Laravel Pint
   - PHP code style fixer
   - PSR-12 based

2. PHPStan
   - Static analysis
   - Level 8 (max)

## Monitoring & Logging
1. Horizon Dashboard: /horizon
2. Telescope Dashboard: /telescope
3. Mailpit Interface: :8025
4. MinIO Console: :8900