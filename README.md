# SaaSBase API

A modern Laravel-based multi-tenant SaaS backend with PostgreSQL, Redis, and a modular architecture. Includes GitHub Actions for CI, static analysis (Larastan), and tests (PHPUnit).

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
- [ ] 5.3 Universal reminders system (polimorficzny model, np. `Reminder`)  
- [x] 5.4 Powiadomienia w systemie (w tym na żywo, WebSockets)  

### 6. Faktury / Koszty  
- [ ] 6.1 Przeglądanie listy faktur z wyszukiwarką
- [ ] 6.3 Akcje na fakturach/kosztach:  
  - [ ] 6.3.1 Zmiana statusu  
  - [ ] 6.3.2 Kopiowanie / klonowanie  
  - [ ] 6.3.3 Wysyłka e-mail  
  - [ ] 6.3.4 Wysyłka do KSeF  
  - [x] 6.3.5 Generowanie PDF i PDF duplikatu (własne szablony wydruków)
  - [ ] 6.3.6 Dołączanie / generowanie płatności  
  - [ ] 6.3.7 Ustawianie i zarządzanie przypomnieniami  
  - [ ] 6.3.8 Powiązania z innymi encjami:  
    - Projekt  
    - Użytkownik  
    - Kontrahent  
  - [ ] 6.3.9 Faktury cykliczne / okresowe z konfiguracją szablonu  
  - [ ] 6.3.10 Udostępnianie linku publicznego  
  - [ ] 6.3.11 Eksport do paczki przelewów bankowych (np. Elixir, Videotel)  
  - [ ] 6.3.12 Grupowe / masowe akcje
- [x] 6.4 Mechanizm tagów/etykiet
- [x] Załączniki 
- [ ] 6.5 Eksport do Excela (lista faktur/kosztów)
- [ ] 6.6 Wymiary kontrolingowe (allocation dimensions) (backend ready) 
- [ ] 6.7 Approvals with configurable paths (backend ready) 

### 7. Produkty  
- [ ] 7.1 Przeglądanie listy produktów z wyszukiwarką
- [ ] 7.2 Etykiety
- [ ] 7.3 Komentarze
- [ ] 7.4 Obrazek logo produktu
- [ ] 7.5 Załączniki 
- [ ] 7.4 Log aktywności 

## 8. Globalne Kontakty (In progress)  
- [ ] 8.1 Zarządzanie globalnymi kontaktami (tworzenie, edycja, usuwanie)  
- [ ] 8.2 Powiązanie globalnych kontaktów z kontrahentami  
- [ ] 8.3 Integracja z systemem adresów i osób kontaktowych  
- [ ] 8.4 Przeszukiwanie i paginacja globalnych kontaktów  

## 9. Komunikacja i Chat  
- [ ] 9.1 System chatu pomiędzy użytkownikami  
- [x] 9.2 Chat AI (integracja z OpenRouter)  

## 10. Projekty  
- [ ] 10.1 Zarządzanie projektami (tworzenie, edycja, usuwanie)  
- [ ] 10.2 Zarządzanie zadaniami w projektach  
- [ ] 10.3 Rejestracja czasu pracy (timesheet)  

## 11. Subscription  
- [x] 11.1 Różne plany subskrypcyjne (np. Free, Basic, Pro, Enterprise)  
- [ ] 11.2 Zarządzanie subskrypcją w panelu użytkownika  
- [x] 11.3 Płatności online przez Stripe (karty kredytowe, faktury)  
- [ ] 11.4 Automatyczne odnawianie subskrypcji  
- [ ] 11.5 Blokady funkcji po wygaśnięciu płatności


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
 
