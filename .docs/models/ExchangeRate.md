# ExchangeRate Model

The ExchangeRate model represents the exchange rate between two currencies on a specific date. It maintains the relationship between base and target currencies along with the conversion rate.

## Attributes
- `id` (uuid) - Primary key
- `exchange_id` (uuid) - Reference to the base currency Exchange model
- `target_exchange_id` (uuid) - Reference to the target currency Exchange model
- `rate` (decimal:6) - Exchange rate value between the currencies
- `date` (date) - Date when this rate was valid
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships
- `exchange()` - BelongsTo Exchange, returns the base currency Exchange model
- `targetExchange()` - BelongsTo Exchange, returns the target currency Exchange model

## Usage
The model is primarily used for currency conversion and rate tracking:

```php
// Get latest USD to PLN rate
$usdRate = ExchangeRate::query()
    ->whereHas('exchange', fn($q) => $q->where('code', 'USD'))
    ->whereHas('targetExchange', fn($q) => $q->where('code', 'PLN'))
    ->whereDate('date', now())
    ->first();

// Convert amount
$amount = 100;
$plnAmount = $amount * $usdRate->rate;
```

## Business Rules
1. Data Sources:
   - Primary: NBP API (Polish National Bank)
   - Secondary: European Central Bank (planned)

2. Rate Updates:
   - Rates are imported daily at 16:15 via scheduled command
   - Command: `php artisan exchange:import`

3. Error Handling:
   - Failed API calls use retry logic
   - System falls back to last known rates
   - Admin notifications for persistent failures

4. Validation Rules:
   - Rate values must be positive decimals
   - Date must be valid and not in future
   - Currency codes must exist in Exchange table

5. Testing Requirements:
   - Unit tests for relationships and calculations
   - Integration tests for API and import process

## Model Definition

```php
namespace App\Domain\Exchange\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    protected $fillable = [
        'exchange_id',          // Base currency
        'target_exchange_id',   // Target currency
        'rate',                 // Exchange rate value
        'date',                // Date of the rate
    ];

    protected $casts = [
        'date' => 'date',
        'rate' => 'decimal:6'
    ];

    public function exchange(): BelongsTo
    {
        return $this->belongsTo(Exchange::class);
    }

    public function targetExchange(): BelongsTo
    {
        return $this->belongsTo(Exchange::class, 'target_exchange_id');
    }
}
```

## API Endpoints

The system provides read-only access to exchange rates:

```php
// routes/api.php
Route::prefix('api')->group(function () {
    Route::get('exchange-rates', [ExchangeRateController::class, 'index']);
    Route::get('exchange-rates/{id}', [ExchangeRateController::class, 'show']);
});
```

## Data Sources

### Primary Source: NBP API
The Polish National Bank (NBP) API serves as the primary source for exchange rates:

```php
namespace App\Domain\Exchange\Services;

use Illuminate\Support\Facades\Http;

class NbpExchangeService
{
    protected string $baseUrl = 'http://api.nbp.pl/api';

    public function getLatestRates(): array
    {
        $response = Http::get("{$this->baseUrl}/exchangerates/tables/A");
        return $response->json();
    }

    public function getRatesByDate(string $date): array
    {
        $response = Http::get("{$this->baseUrl}/exchangerates/tables/A/{$date}");
        return $response->json();
    }
}
```

### Secondary Source (Future)
Plan to integrate a secondary source (e.g., European Central Bank) for redundancy.

## Scheduled Updates

### Daily Rate Import
```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Exchange\Services\NbpExchangeService;

class ImportExchangeRates extends Command
{
    protected $signature = 'exchange:import';
    protected $description = 'Import latest exchange rates from NBP';

    public function handle(NbpExchangeService $service)
    {
        $rates = $service->getLatestRates();
        // Process and store rates
    }
}
```

Add to scheduler:
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('exchange:import')->dailyAt('16:15');
}
```

## Error Handling

1. API Failures:
   - Retry logic for failed API calls
   - Fallback to last known rates
   - Admin notifications for persistent failures

2. Data Validation:
   - Rate value validation
   - Date consistency checks
   - Currency code validation

## Testing

1. Unit Tests:
   - Model relationships
   - Rate calculations
   - Date handling
   - Decimal precision

2. Integration Tests:
   - NBP API integration
   - Scheduled import process
   - Error handling scenarios

## Usage Example

```php
// Get latest USD to PLN rate
$usdRate = ExchangeRate::query()
    ->whereHas('exchange', fn($q) => $q->where('code', 'USD'))
    ->whereHas('targetExchange', fn($q) => $q->where('code', 'PLN'))
    ->whereDate('date', now())
    ->first();

// Convert amount
$amount = 100;
$plnAmount = $amount * $usdRate->rate;
```

See also: [Exchange Model](./Exchange.md) for currency-specific documentation. 
