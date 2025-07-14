# Polish Ministry of Finance VAT Payer Status API Documentation

## Overview
The Polish Ministry of Finance provides an API for checking VAT taxpayer status through the VAT Taxpayer Registry (API Wykazu podatników VAT). This service allows verification of VAT registration status for Polish entities.

## Base URLs
- **Test Environment**: `https://wl-test.mf.gov.pl/`
- **Production Environment**: `https://wl-api.mf.gov.pl/`

## Authentication
- API access requires registration and approval from the Ministry of Finance
- Contact: `regon_bir@stat.gov.pl` for access requests
- IP address whitelisting may be required

## Service Status
⚠️ **Important Notice**: As of July 29, 2022, the Ministry of Finance stopped providing a dedicated API for checking VAT taxpayer status. The service has been replaced with the National Tax Administration's VAT Taxpayer List API.

## Available Methods

### 1. Full Search Method (`search`)
- **Purpose**: Comprehensive entity lookup with full dataset
- **Parameters**: NIP, REGON, company name fragment, or bank account number
- **Rate Limit**: 100 queries per day, maximum 30 entities per query
- **Returns**: Complete entity information with electronic verification key

### 2. Simplified Check Method (`check`)
- **Purpose**: Quick validation of account-entity association
- **Parameters**: NIP, REGON, account number, specific date
- **Rate Limit**: 5000 queries per day
- **Returns**: YES/NO confirmation with electronic verification key

## Query Parameters
- **NIP**: Polish Tax Identification Number (10 digits)
- **REGON**: Statistical Number (9 or 14 digits)
- **Bank Account Number**: Polish bank account number
- **Date**: Specific date for historical verification (YYYY-MM-DD format)

## Rate Limits
- **Search Method**: 100 queries/day, max 30 entities simultaneously
- **Check Method**: 5000 queries/day
- Temporary blocking may occur if limits are exceeded
- Limits reset daily at midnight

## Alternative Data Access
For high-volume verification needs:
- **Daily Flat File**: Published at midnight with comprehensive VAT taxpayer data
- **Format**: Cryptographically secured NIP-account pairs
- **Use Case**: Mass transaction verification without API rate limits

## Response Format
- JSON format with structured entity information
- Includes electronic verification key for audit purposes
- Contains VAT registration status and entity details

## Error Handling
- Rate limit exceeded: Temporary service blocking
- Invalid parameters: Error response with details
- Service unavailable: Fallback to daily flat file recommended

## Integration Notes
- Designed primarily for government and authorized entities
- Commercial access requires special approval
- Service reliability depends on system load and maintenance windows
- Keep verification keys for audit trail purposes

## Current Status
- Service experiencing intermittent availability issues
- API may be temporarily unavailable due to system maintenance
- Monitor official Ministry of Finance announcements for service updates