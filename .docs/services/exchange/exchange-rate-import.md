# Exchange Rate Import Service

Service responsible for importing and maintaining up-to-date currency exchange rates from multiple sources.

## Primary Source: NBP (National Bank of Poland)

### Integration Details
- API Endpoint: `http://api.nbp.pl/api/exchangerates/tables/{table}`
- Update Frequency: Daily (except weekends and holidays)
- Tables Used: A (main currencies), B (other currencies)
- Error Handling: Falls back to secondary source if NBP API fails

### Data Processing
1. Fetches latest rates from NBP API
2. Validates response data
3. Updates `exchange_rates` table
4. Logs import results

## Secondary Source: European Central Bank

### Integration Details
- API Endpoint: `https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml`
- Used as fallback when NBP is unavailable
- Provides EUR-based rates

## Schedule

The service runs:
- Main import: Every working day at 12:00 PM CET
- Validation check: Every working day at 2:00 PM CET
- Weekend rates are copied from Friday

## Error Handling

1. Source API Unavailable
   - Logs error
   - Switches to secondary source
   - Notifies admin if both sources fail

2. Invalid Data
   - Logs error with details
   - Keeps previous valid rates
   - Notifies admin

## Models Used

- `Exchange` - Currency definitions
- `ExchangeRate` - Daily rates storage

## Configuration

Located in `config/services.php`:
```php
'exchange_rates' => [
    'primary_source' => 'nbp',
    'secondary_source' => 'ecb',
    'notification_email' => 'admin@example.com',
    'retry_attempts' => 3,
]
``` 
