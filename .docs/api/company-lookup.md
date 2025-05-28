# Company Lookup Services

This document describes the company lookup services available in the application. These services provide integration with various official registries to fetch company information.

## Available Services

### 1. REGON Lookup Service

The REGON Lookup Service integrates with the Polish National Business Registry (REGON) to fetch company information.

**Features:**
- Lookup by NIP (Polish VAT number)
- Lookup by REGON number
- Caching support
- Comprehensive company details including:
  - Company name and short name
  - REGON and NIP numbers
  - Address information
  - Contact details (phone, email, website)
  - Registration dates
  - Legal form and status

**Example Usage:**
```php
use App\Services\RegonLookup\Services\RegonLookupService;

$service = app(RegonLookupService::class);
$company = $service->findByNip('1234567890');
```

### 2. Ministry of Finance (MF) Lookup Service

The MF Lookup Service integrates with the Polish Ministry of Finance database to fetch company information.

**Features:**
- Lookup by NIP
- Caching support
- Comprehensive company details including:
  - Company name
  - NIP number
  - REGON and KRS numbers
  - Address information
  - Bank account numbers
  - VAT status
  - Representatives and authorized clerks

**Example Usage:**
```php
use App\Services\MfLookup\Services\MfLookupService;

$service = app(MfLookupService::class);
$company = $service->findByNip('1234567890');
```

### 3. VIES Lookup Service

The VIES Lookup Service integrates with the European Union's VAT Information Exchange System to verify VAT numbers.

**Features:**
- VAT number validation
- Company information retrieval
- Support for all EU member states
- SOAP-based integration

**Example Usage:**
```php
use App\Services\ViesLookup\Services\ViesLookupService;

$service = app(ViesLookupService::class);
$result = $service->findByVat('PL1234567890');
```

## Common Data Structure

All lookup services return data in a standardized format through the `CommonCompanyLookupData` DTO:

```php
class CommonCompanyLookupData
{
    public function __construct(
        public string $name,
        public string $country,
        public ?string $vatId = null,
        public ?string $regon = null,
        public ?string $shortName = null,
        public ?string $phoneNumber = null,
        public ?string $email = null,
        public ?string $website = null,
        public ?AddressDTO $address = null,
        public ?BankAccountDTO $bankAccount = null,
    ) {}
}
```

## Auto-fill Service

The `CompanyDataAutoFillService` provides a unified interface to fetch company information from multiple sources:

```php
use App\Domain\Utils\Services\CompanyDataAutoFillService;

$service = app(CompanyDataAutoFillService::class);
$company = $service->autoFill(
    nip: '1234567890',
    regon: '123456789',
    force: false
);
```

The service will:
1. Try to fetch data from REGON if a REGON number is provided
2. Try to fetch data from MF if a NIP is provided
3. Merge the data, preferring REGON for company information and MF for bank account details

## Error Handling

All services implement proper error handling and will throw specific exceptions:

- `RegonLookupException` for REGON service errors
- `MfLookupException` for MF service errors
- `ViesLookupException` for VIES service errors

## Caching

All services implement caching to reduce API calls:

- Default cache duration: 12 hours
- Configurable through `COMPANY_LOOKUP_CACHE_HOURS` environment variable
- Alternative weekly cache mode available through `COMPANY_LOOKUP_CACHE_MODE=week`

## API Endpoints

### Company Lookup

```
GET /api/v1/utils/company-lookup
```

**Parameters:**
- `vatId` (required): The VAT number to look up
- `country` (required): The country code (2 characters)
- `force` (optional): Force refresh the cache (admin only)

**Response:**
```json
{
    "country": "PL",
    "name": "Example Company Sp. z o.o.",
    "vatId": "1234567890",
    "regon": "123456789",
    "shortName": "Example",
    "phoneNumber": "+48123456789",
    "email": "contact@example.com",
    "website": "https://example.com",
    "address": {
        "country": "PL",
        "city": "Warszawa",
        "type": "REGISTERED_OFFICE",
        "isDefault": true,
        "street": "ul. Kwiatowa 15",
        "building": "15",
        "flat": "1",
        "postalCode": "00-001"
    },
    "bankAccount": {
        "iban": "PL10105000997603123456789123",
        "isDefault": true
    }
}
```

## Command Line Tools

The services provide command-line tools for testing and debugging:

```bash
# REGON Lookup
php artisan regon:lookup 1234567890 --force

# MF Lookup
php artisan mf:lookup 1234567890
```

## Configuration

The services can be configured through environment variables:

```env
COMPANY_LOOKUP_CACHE_HOURS=12
COMPANY_LOOKUP_CACHE_MODE=hours  # or 'week'
REGON_LOOKUP_USER_KEY=your-key-here
```

## Best Practices

1. Always use the `CompanyDataAutoFillService` for fetching company information
2. Handle exceptions appropriately
3. Use caching to reduce API calls
4. Validate input data before making API calls
5. Use the command-line tools for testing
6. Monitor API usage and implement rate limiting if needed 
