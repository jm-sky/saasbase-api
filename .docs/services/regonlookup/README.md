# Polish REGON API (GUS) Documentation

## Overview
The REGON API (BIR1 - Baza Internetowa REGON) is provided by GUS (Główny Urząd Statystyczny - Polish Central Statistical Office). It provides access to the official REGON register containing information about Polish business entities.

## Service Details
- **Official Name**: BIR1 (Baza Internetowa REGON)
- **Provider**: GUS (Central Statistical Office of Poland)
- **Type**: SOAP/XML Web Service with JSON wrapper
- **Cost**: Free of charge

## Base URLs
- **Production**: Available after registration approval
- **Sandbox**: Available for testing without API key (anonymized data)

## Registration Process

### For Commercial Entities
**Contact**: `regon_bir@stat.gov.pl`

**Required Information**:
- Entity details and contact information
- IP addresses for API access
- Expected monthly query volume
- Business justification for data access

### For Public Administration
- Submit application with institutional details
- Implementation stages agreed directly with GUS
- Typically faster approval process

**Phone Support**: 22 608-36-39 or 22 608-33-74

## Authentication
- API Key required for production environment
- IP address whitelisting may be required
- Sandbox mode available for testing (no API key needed)

## Search Capabilities
The API supports entity lookup using:
- **NIP**: Tax Identification Number
- **REGON**: Statistical Number (9 or 14 digits)
- **KRS**: Court Registration Number

## Available Reports
The service provides multiple report types with detailed entity information:

### Individual Reports
- `BIR11OsFizycznaDaneOgolne` - General data for physical persons
- `BIR11OsFizycznaDzialalnoscCeidg` - CEIDG business activity data
- `BIR11OsFizycznaDzialalnoscRolnicza` - Agricultural activity data

### Legal Entity Reports
- `BIR11OsPrawna` - Legal entity general data
- `BIR11OsPrawnaPkd` - PKD (business classification) codes
- `BIR11OsPrawnaListaJednLokalnych` - List of local units
- `BIR11JednLokalnaOsPrawnej` - Local unit details
- `BIR11OsPrawnaSpCywilnaWspolnicy` - Civil partnership members
- `BIR11TypPodmiotu` - Entity type information

## Data Structure
### Common Fields
- Company name and legal form
- Registration dates and status
- Address information (headquarters and correspondence)
- Contact details (phone, email, website)
- Business activity codes (PKD)
- Employment data
- Financial information

### Address Components
- Postal code and country
- City/municipality name
- Street name and number
- Voivodeship (province)

## Rate Limits
- No explicit rate limits mentioned in public documentation
- Service designed for reasonable usage patterns
- Bulk operations may require special arrangements

## Response Format
- Original SOAP XML responses converted to JSON
- Preserves original field structure and naming conventions
- Mix of snake_case and camelCase field names (as per GUS standards)
- All values converted to strings in JSON output

## Service Statistics
- **Total Queries**: 26,690,789,803 (since 01.01.2015)
- **Registered Users**: 15,909
- High availability and reliability

## Technical Implementation
- SOAP web service with XML schema
- JSON wrapper available for easier integration
- Multiple client libraries available (PHP, Python, Node.js, etc.)
- Comprehensive documentation in downloadable technical manuals

## Data Quality
- Official government registry data
- Real-time updates from business registration authorities
- High data accuracy and completeness
- Historical data preservation

## Use Cases
- Business verification and due diligence
- Know Your Customer (KYC) processes
- Market research and analysis
- Integration with business systems
- Compliance and regulatory reporting

## Documentation Resources
- Technical manuals available for download (versions 1.1 and 0.1)
- Detailed field specifications in BIR11_StrukturyDanych folder
- Code examples and integration guides
- SOAP WSDL specifications