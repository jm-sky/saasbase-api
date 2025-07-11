# SaaSBase API

A modern Laravel-based multi-tenant SaaS backend with PostgreSQL, Redis, and a modular architecture. Includes GitHub Actions for CI, static analysis (Larastan), and tests (PHPUnit).

![dashboard](https://raw.githubusercontent.com/jm-sky/saasbase-api/refs/heads/develop/public/images/dashboard.png)

## Features

### 1. Autoryzacja
- [x] 1.1 Rejestracja użytkownika  
- [x] 1.2 Logowanie (hasło)
- [x] 1.3 Reset hasła 
- [x] 1.4 OAuth (Google, GitHub)  
- [x] 1.5 Zaproszenia użytkowników w ramach tenant  

### 2. Tenant  
- [x] 2.1 Rejestracja tenanta  
- [x] 2.2 Adresy tenanta  
- [x] 2.3 Konta bankowe tenanta  
- [x] 2.4 Profil publiczny tenanta (partially)
- [x] 2.5 Branding tenanta (partially) 
- [x] 2.6 Struktura organizacji tenanta, stanowiska, role systemu uprawnień
- [x] 2.7 Zaproszenia w ramach tenanta  
- [ ] 2.8 Konfiguracja integracji (tenant podaje własne credentials):  
  - [x] 2.8.1 Azure Intelligence Studio  
  - [x] 2.8.2 KSeF (Polski system e-faktur) (partially) 
  - [x] 2.8.3 E-doręczenia (eDO Post) (not tested) 
  - [ ] 2.8.4 Integracja z pocztą e-mail  
  - [ ] 2.8.5 Integracja z Google Kalendarzem  

### 3. Integracje  
- [x] 3.1 REGON
- [x] 3.2 VIES
- [x] 3.3 Ministerstwo Finansów - Biała Lista Podatników VAT  
- [x] 3.4 IBAN API 
- [x] 3.5 Azure Intelligence Studio (OCR)
- [x] 3.6 Płatności online przez Stripe 

### 4. Kontrahenci  
- [x] 4.1 Lista kontrahentów z wyszukiwarką (full text search) 
- [x] 4.2 Adresy kontrahentów  
- [x] 4.3 Konta bankowe kontrahentów 
- [x] 4.4 Osoby kontaktowe (todo: powiązanie z domeną Globalne Kontakty)  
- [x] 4.5 Obrazek/logo kontrahenta  
- [x] 4.6 Registry Confirmation (potwierdzenia zgodności z rejestrami REGON, VIES, Ministerstwo Finansów)  
- [x] 4.7 Mechanizm tagów/etykiet
- [x] 4.8 Mechanizm komentarzy
- [x] 4.9 Eksport do Excela (lista kontrahentów)
- [x] Załączniki 
- [x] 4.10 Log aktywności 

### 5. Common / Shared Functionalities  
- [x] 5.1 Wspólny mechanizm przeszukiwania i paginacji dla list (wsparcie operatorów porównania, filtrów, sortowania)  
- [ ] 5.2 Universal "Link with..." funkcjonalność z polimorficznym modelem (np. `Linkable`)  
## 📦 Features

### 1. Authentication

- [x] 1.1 User registration  
- [x] 1.2 Login (email & password)  
- [x] 1.3 Password reset  
- [x] 1.4 OAuth login (Google, GitHub)  
- [x] 1.5 User-to-user invitations *(outside tenant scope)*

---

### 2. Tenant Management

- [x] 2.1 Tenant registration  
- [x] 2.2 Tenant addresses  
- [x] 2.3 Tenant bank accounts  
- [x] 2.4 Tenant organization: roles, positions, permissions  
- [x] 2.5 Internal tenant invitations  
- [ ] 2.6 Tenant public profile ⏳  
- [ ] 2.7 Tenant branding ⏳  
- [x] 2.8 Configurable third-party integrations (per-tenant credentials):  
  - [x] 2.8.1 Azure Document Intelligence  
  - [ ] 2.8.2 KSeF (Polish national e-invoicing system) ⏳  
  - [ ] 2.8.3 E-Delivery integration (Polish eDO Post) 🧪  
  - [ ] 2.8.4 Email account integration
  - [ ] 2.8.5 Google Calendar integration

---

### 3. Integrations

- [x] 3.1 REGON – Polish national business registry (official ID system for all economic entities)  
- [x] 3.2 VIES – EU-wide VAT registry for validating VAT numbers across member states  
- [x] 3.3 Ministry of Finance – Polish VAT white list registry  
- [x] 3.4 IBAN API – validates IBANs and enriches them with bank name, country, institution type  
- [x] 3.5 Azure Document Intelligence (OCR for invoices and documents)  
- [x] 3.6 Stripe – online payments & invoicing  

---

### 4. Contractors

- [x] 4.1 Full-text search & list view  
- [x] 4.2 Contractor addresses  
- [x] 4.3 Contractor bank accounts  
- [x] 4.4 Contact persons (global contacts in progress ⏳)
- [x] 4.5 Contractor logo/image  
- [x] 4.6 Registry confirmations (REGON, VIES, MF)  
- [x] 4.7 Tags
- [x] 4.8 Comments
- [x] 4.9 Export to Excel  
- [x] 4.10 Attachments  
- [x] 4.11 Activity log  

---

### 5. Shared Features (Common)

- [x] 5.1 Unified pagination, filtering, sorting  
- [ ] 5.2 Universal "Link with..." (polymorphic `Linkable` model)
- [ ] 5.3 Universal reminders (`Reminder`, polymorphic)
- [x] 5.4 Notifications system (including WebSocket support)  

---

### 6. Invoices & Expenses

- [x] 6.1 List & search invoices
- [x] 6.2 Attachments  
- [x] 6.3 Tags  
- [ ] 6.4 Export to Excel
- [ ] 6.5 Invoice actions:  
  - [ ] 6.3.1 Status changes
  - [ ] 6.3.2 Clone / copy
  - [ ] 6.3.3 Send by email
  - [ ] 6.3.4 Send to KSeF
  - [x] 6.3.5 Generate PDF / duplicate (custom templates)  
  - [ ] 6.3.6 Generate / attach payment
  - [ ] 6.3.7 Reminders
  - [ ] 6.3.8 Link to project, user, contractor
  - [ ] 6.3.9 Recurring invoices / templates
  - [ ] 6.3.10 Public shareable link
  - [ ] 6.3.11 Export to Elixir/Videotel batch transfers
  - [ ] 6.3.12 Bulk actions
- [ ] 6.6 Controlling dimensions (cost allocation)
- [ ] 6.7 Approval workflows with routing (backend-ready)

---

### 7. Products

- [x] 7.1 Product list & search
- [x] 7.2 Tagging
- [x] 7.3 Comments
- [x] 7.4 Product image/logo
- [x] 7.5 Attachments
- [x] 7.6 Activity log

---

### 8. Global Contacts *(in progress)*

- [ ] 8.1 Global contact management
- [ ] 8.2 Contractor linkage
- [ ] 8.3 Integration with address & contact systems
- [ ] 8.4 Search & pagination

---

### 9. Communication & Chat

- [ ] 9.1 Internal messaging/chat
- [x] 9.2 AI Chat (OpenRouter API)  

---

### 10. Projects

- [ ] 10.1 Projects (CRUD)
- [ ] 10.2 Project tasks
- [ ] 10.3 Timesheet tracking

---

### 11. Subscription

- [x] 11.1 Plans: Free, Basic, Pro, Enterprise  
- [ ] 11.2 Plan management in UI
- [x] 11.3 Stripe billing  
- [ ] 11.4 Auto-renewal
- [ ] 11.5 Account lockout after expiration

---

### Tech

- Laravel 12+ API-only backend
- PostgreSQL & Redis integration
- Docker-ready with Sail-like structure
- JWT-based authentication
- GitHub Actions CI pipeline
- Static analysis with Larastan
- PHPUnit testing support
- S3 storage 

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
- Meilisearch 

---

## Development Setup

### Cursor MCP Configuration

For Task Master integration with Cursor, you need to:

1. Copy `.cursor/mcp.json.example` to `.cursor/mcp.json`
2. Add your API keys to `.cursor/mcp.json`
3. Never commit `.cursor/mcp.json` to the repository (it's gitignored)

Required API keys depend on which AI model you're using with Task Master. Currently configured for OpenRouter.

### Testing Stripe in local mode

1. Install [Stripe CLI](https://github.com/stripe/stripe-cli)
2. Run `stripe listen --forward-to localhost:8989/api/v1/stripe/webhook` in terminal

---

## License

[MIT](LICENSE)
 
