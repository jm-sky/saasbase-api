# Refactor: Company Lookup & Registry Confirmation Implementation Plan

## Context
We're refactoring company lookup functionality with 3 data sources:
- REGON/GUS (currently implemented)
- VIES
- MF (Tax Payers White List)

The system has two main use cases:
1. Auto-filling forms when creating Tenant/Contractor
2. Registry Confirmation - attaching badges to confirm data validity

## Current State
- We have a working GUS lookup service using Saloon
- We have a basic company lookup endpoint in `routes/api/utils.php`
- We have a specification for Registry Confirmation in `.docs/tasks/registry-confirmation.md`

## Implementation Tasks

### 1. Registry Confirmation Model & Migration
- [x] Create a polymorphic model for storing confirmation data
- [x] Create migration with fields:
  - [x] id: UUID
  - [x] confirmable_id: string
  - [x] confirmable_type: string   # Address, BankAccount, etc.
  - [x] type: string               # enum: 'GUS', 'VIES', 'WhiteList'
  - [x] payload: json              # input data (vat_id, iban, etc.)
  - [x] result: json               # registry response, hash, external_id, match_score
  - [x] success: boolean           # confirmation status
  - [x] checked_at: datetime       # verification timestamp
  - [x] created_at: datetime

### 2. Registry Confirmation Resource
- [ ] Create a resource that will:
  - [ ] Transform confirmation data into badge format
  - [ ] Include confidence score
  - [ ] Show verification timestamp
  - [ ] Indicate data source (GUS/VIES/WhiteList)

### 3. Company Data Auto-fill Service
- [x] Create a service (check `CompanyDataAutoFillService`) that will:
  - [x] Accept search parameters (NIP, REGON)
  - [ ] Query all available data sources
  - [ ] Standardize data from different sources
  - [ ] Return unified structure for form auto-fill:
    - [ ] Company header (name, vat_id, regon, short_name)
    - [ ] Contact data (email, phone, website)
    - [ ] Bank account
    - [ ] Address

### 4. Controller Refactoring
- [x] Refactor `CompanyLookupController` to:
  - [x] Use the new auto-fill service
  - [ ] Handle multiple data sources
  - [ ] Return standardized response
  - [ ] Include registry confirmation data

## Implementation Order
- [ ] Create Registry Confirmation model and migration
- [ ] Create Registry Confirmation resource
- [ ] Create Company Data Auto-fill service
- [ ] Refactor controller to use new service
- [ ] Add tests for new functionality

## Notes
- Keep using Saloon for API integrations
- Maintain existing caching strategy
- Follow existing error handling patterns
- Consider rate limiting for all external APIs
- Ensure proper validation of input data
- Add proper logging for debugging

## Expected Outcomes
- [ ] A polymorphic model for storing confirmation data
- [ ] A resource for displaying confirmation badges
- [ ] A service for auto-filling company data
- [ ] A refactored controller using the new service
- [ ] Proper error handling and validation
- [ ] Comprehensive test coverage


# Notes
```
type OfficialCompanyRecord = {
  name: string
  nip: string
  regon?: string
  email?: string
  // maybe something more
  address?: object
  bankAccount?: object
  // Listed sources confirmations
  confirmations: {
    gus: boolean
    vies: boolean
    mf: boolean
    whiteList: boolean
  }
}
```