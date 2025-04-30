
# SaaSBase API

A modern Laravel-based multi-tenant SaaS backend with PostgreSQL, Redis, and a modular architecture. Includes GitHub Actions for CI, static analysis (Larastan), and tests (PHPUnit).

## Features

- Laravel 12+ API-only backend
- PostgreSQL & Redis integration
- Docker-ready with Sail-like structure
- JWT-based authentication
- GitHub Actions CI pipeline
- Static analysis with Larastan
- PHPUnit testing support

---

## Getting Started

### Prerequisites

- Docker + Docker Compose
- PHP 8.3+
- Composer

### Setup

1. Clone the repository:

```bash
git clone https://github.com/jm-sky/saasbase-api.git
cd saasbase-api
```

2. Copy the `.env` file and configure:

```bash
cp .env.example .env
```

3. Start Docker containers:

```bash
docker-compose up -d
```

4. Install PHP dependencies:

```bash
composer install
```

5. Generate the application key:

```bash
php artisan key:generate
php artisan jwt:secret
```

6. Run database migrations:

```bash
php artisan migrate
```

---

## Running Tests

Run PHPUnit tests:

```bash
php artisan test
```

Or directly:

```bash
vendor/bin/phpunit
```

Run Larastan static analysis:

```bash
vendor/bin/phpstan analyse
```

---

## CI/CD

This repository includes a GitHub Actions workflow:

- Validates code with Larastan
- Runs PHPUnit tests
- Uses PostgreSQL service
- Caches Composer dependencies

### Triggered on:

- Push to `main`
- Pull request to `main`

---

## Docker Services

The default `docker-compose.yml` includes:

- `pgsql`: PostgreSQL 17
- `redis`: Redis (for cache & queues)
- `minio`: S3-compatible storage
- `mailpit`: Dev SMTP server
- `soketi`: WebSocket server

---

## License

[MIT](LICENSE)
