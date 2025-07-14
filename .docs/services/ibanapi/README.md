# IbanApi.com Service Documentation

## Overview
IbanApi.com provides a freemium REST API for IBAN validation with comprehensive bank information retrieval. The service offers both basic and full validation endpoints with detailed bank and SEPA data.

## Base URL
- Production: `https://api.ibanapi.com/v1`

## Authentication
The API supports three authentication methods:
1. **URL Parameter**: `?api_key=YOUR_API_KEY`
2. **POST Form Variable**: Include `api_key` in form data
3. **Authorization Header**: `Authorization: Bearer YOUR_API_KEY`

## Rate Limits
- Free tier: Limited requests per month
- Paid plans: Higher limits available
- Daily query limits apply

## Key Features
- IBAN validation with mathematical check
- Bank information retrieval
- SEPA compatibility checks
- Country-specific validation
- Bulk validation support
- JSONP support for client-side applications

## Supported Countries
All IBAN-supporting countries including EU member states and associated territories.

## Error Handling
The API returns HTTP status codes along with detailed error messages:
- `200`: Success
- `400`: Bad Request (invalid IBAN format)
- `401`: Unauthorized (invalid API key)
- `429`: Too Many Requests (rate limit exceeded)
- `500`: Internal Server Error

## Client Libraries
Official libraries available for:
- PHP
- Node.js
- Go
- Postman Collection
- RapidAPI integration

## Response Format
All responses are in JSON format with consistent structure containing:
- `result`: HTTP status code
- `message`: Human-readable status message
- `validations`: Detailed validation checks
- `data`: Bank and country information (if available)