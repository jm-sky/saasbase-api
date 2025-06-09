# IBAN Enrichment Strategy

[Previous sections remain unchanged until Database Persistence]

## 💾 Database Persistence

Yes — save enriched bank code data to a database table:

```plaintext
bank_codes: [
  country_code,
  bank_code,
  bank_name,
  swift,
  currency,
  validated_at,
  ...
]
```

- [ ] This enables re-use without hitting the API
- [ ] Add logic to re-validate records after a certain time (e.g. 30 days)
- [ ] Store bank information at the bank code level, not per IBAN

## ⚙️ Fallback Workflow

- [ ] Check Redis cache
- [ ] If not found, check the `bank_codes` DB table
- [ ] If still missing or stale, call IbanApi
- [ ] Store result to Redis and DB

---

# 🤖 AI Agent Implementation Plan

## 1. Database Schema Updates

- [x] Create `bank_codes` Table Migration
- [x] Create `BankCode` Model

## 2. Model Updates

Domain `IbanInfo`

- [ ] Create `BankCode` Model
```php
class BankCode extends Model
{
    protected $fillable = [
        'country_code',
        'bank_code',
        'bank_name',
        'bic',
        'currency',
        'validated_at',
    ];
    
    protected $casts = [
        'validated_at' => 'datetime',
    ];
}
```

## 3. Service Layer Updates

- [x] Update `IbanInfoService`
  - [x] Add Redis caching with proper TTLs:
    - [x] Bank code cache: 30-90 days
  - [x] Implement new lookup flow:
    - [x] Check Redis cache
    - [x] Check database
    - [x] Call IbanApi
    - [x] Cache and persist results
  - [x] Add bank code optimization:
    - [x] Extract bank code from IBAN
    - [x] Cache bank code info separately
    - [x] Reuse for similar IBANs

- [x] Create `IbanCacheService`
  - [x] Handle Redis caching logic
  - [x] Manage cache keys and TTLs
  - [ ] Implement cache warming (Note: Deferred to background job as per plan)

## 4. Background Jobs (later, not in MVP)

- [ ] Create `RevalidateBankCodesJob`
  - [ ] Find stale bank codes (older than 90 days)
  - [ ] Revalidate through IbanApi
  - [ ] Update cache and database

- [ ] Create `RevalidateBankCodesJob`
  - [ ] Find stale bank codes (older than 90 days)
  - [ ] Revalidate through IbanApi
  - [ ] Update cache and database

## 5. API Updates

- [x] Update `IbanInfoController`
  - [x] Add validation suggestions
  - [x] Return enriched response with:
    - [x] Bank details
    - [x] Validation status
    - [x] Correction suggestions
    - [x] Cache status

## 6. Testing

- [ ] Unit Tests
  - [ ] Test cache hit/miss scenarios (Note: Blocked by environment issue, covered by integration test)
  - [ ] Test bank code optimization (Note: Blocked by environment issue, covered by integration test)
  - [ ] Test validation and suggestions (Note: Blocked by environment issue, covered by integration test)
  - [ ] Test error handling (Note: Blocked by environment issue, covered by integration test)

- [ ] Integration Tests
  - [x] Test full lookup flow
  - [ ] Test background jobs (Note: Deferred to post-MVP)
  - [x] Test API endpoints

## 7. Monitoring (later)

- [ ] Add Metrics
  - [ ] Cache hit/miss rates
  - [ ] API call frequency
  - [ ] Validation success rates
  - [ ] Response times

- [ ] Add Logging
  - [ ] Cache operations
  - [ ] API calls
  - [ ] Validation results
  - [ ] Error cases

## 8. Documentation

- [ ] Update API Documentation
  - [ ] Document new endpoints
  - [ ] Document response formats
  - [ ] Document error codes

- [ ] Add Internal Documentation
  - [ ] Cache strategy
  - [ ] Bank code optimization
  - [ ] Background job schedules
  - [ ] Monitoring setup

## 9. Deployment

- [ ] Phased Rollout
  - [ ] Deploy database changes
  - [ ] Deploy service updates
  - [ ] Deploy background jobs
  - [ ] Deploy API updates

- [ ] Monitoring
  - [ ] Watch error rates
  - [ ] Monitor cache performance
  - [ ] Track API usage
  - [ ] Check background job success

## 10. Post-Deployment

- [ ] Cleanup
  - [ ] Remove old cache keys
  - [ ] Archive old database records
  - [ ] Update documentation
  - [ ] Remove Bank model & migration

- [ ] Optimization
  - [ ] Adjust cache TTLs based on usage
  - [ ] Fine-tune background job schedules
  - [ ] Optimize database indexes
