# VIES (VAT Information Exchange System) API Documentation

## Overview
VIES is the European Commission's official VAT number validation system that allows checking the validity of VAT registration numbers for EU member states. The system provides real-time validation through a SOAP web service.

## Service Details
- **Provider**: European Commission - DG Taxation and Customs Union
- **Type**: SOAP Web Service
- **Protocol**: HTTP/HTTPS
- **Format**: XML
- **Cost**: Free of charge

## Base URLs
- **WSDL**: `http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl`
- **Service Endpoint**: `http://ec.europa.eu/taxation_customs/vies/services/checkVatService`

## Supported Countries
All EU member states plus some associated territories:
- Austria (AT), Belgium (BE), Bulgaria (BG), Croatia (HR), Cyprus (CY)
- Czech Republic (CZ), Denmark (DK), Estonia (EE), Finland (FI), France (FR)
- Germany (DE), Greece (GR), Hungary (HU), Ireland (IE), Italy (IT)
- Latvia (LV), Lithuania (LT), Luxembourg (LU), Malta (MT), Netherlands (NL)
- Poland (PL), Portugal (PT), Romania (RO), Slovakia (SK), Slovenia (SI)
- Spain (ES), Sweden (SE)

## Authentication
- No API key required
- No registration needed
- Open access for all users

## Rate Limits
- No explicit rate limits
- Service designed for reasonable usage
- Heavy usage may trigger temporary restrictions
- Service availability depends on member state systems

## Available Operations

### 1. Simple VAT Validation (`checkVat`)
Basic validation requiring only country code and VAT number.

### 2. Approximate VAT Validation (`checkVatApprox`)
Enhanced validation that includes requester information for audit purposes.

## Service Characteristics

### Network Architecture
- Queries are dispatched to individual member state systems
- Uses legacy Customs network (CCN/CSI)
- Real-time validation (not a central database)
- Response time varies by member state

### Reliability Considerations
- Member state systems may be temporarily unavailable
- Maintenance windows can affect service availability
- Some countries have more reliable systems than others
- Network latency can vary significantly

### Audit Trail
- Each query receives a unique consultation number (requestIdentifier)
- Keep consultation numbers for tax administration proof
- Queries are logged for compliance purposes

## Response Data
Successful validation provides:
- VAT number validity status
- Company name (if available)
- Company address (if available)
- Request timestamp
- Unique consultation identifier

## Error Handling
Common error scenarios:
- Invalid VAT number format
- VAT number not registered
- Member state system unavailable
- Service temporarily unavailable
- Invalid country code

## Integration Best Practices

### Caching Strategy
- Cache valid results for reasonable periods
- Re-validate periodically for compliance
- Handle cache misses gracefully

### Error Handling
- Implement retry logic for temporary failures
- Provide fallback mechanisms
- Log errors for monitoring

### Performance Optimization
- Batch validation where possible
- Implement timeout handling
- Monitor response times by country

## Compliance Notes
- Service is provided for legitimate business purposes
- Results are legally recognized across EU
- Maintain audit logs of validations performed
- Keep consultation numbers for tax compliance

## Technical Limitations
- XML/SOAP only (no REST API)
- No bulk validation endpoint
- Limited to EU VAT numbers only
- Real-time dependency on member state systems

## Alternative Access Methods
- Web interface available at: https://ec.europa.eu/taxation_customs/vies/
- Third-party API wrappers available
- Client libraries for major programming languages

## Support and Documentation
- Technical support through EC contact forms
- FAQ available on official website
- WSDL provides complete technical specification
- Community forums for implementation help

## Service Status
- Generally high availability
- Periodic maintenance notifications
- Member state outages reported via official channels
- Service updates announced on EC website