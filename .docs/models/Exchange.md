# Exchange Model

Represents a currency exchange entity in the system, used for managing different currencies and their exchange rates.

## Attributes
- `id` (uuid) - Primary key
- `name` (string) - Full name of the currency (e.g., "US Dollar")
- `currency` (string) - Currency code (e.g., "USD")
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships
- `rates` - HasMany relationship to ExchangeRate
- `invoices` - HasMany relationship to Invoice (where this is the base currency)

## Usage
The Exchange model is used to:
- Define available currencies in the system
- Support multi-currency invoicing
- Enable currency conversion in financial operations

## API Endpoints
- `GET /api/exchanges` - List all available currencies
- `GET /api/exchanges/{id}` - Get specific currency details

## Business Rules
1. Currency codes must be unique
2. Currency codes should follow ISO 4217 standard
3. Exchange rates must be updated daily via scheduled task 
