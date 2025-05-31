# Tidy controllers and tests

- We should check Laravel v12 documentation if problems occurred. 
- We use PHP CS Fixer with `composer csf` command.
- We use Docker with Sail.
- We should focus plan and progress (checkboxed) in markdown file. 

1. Check all controllers
    - Should return Resource or Resource collection
    - Should implement Request for store/update. 
    - Should use indexQuery (trait & query for filtering) where needed - where filtering is needed, or everywhere to be consistent). 
    - Should define reasonable default perPage property for pagination. Usually around 20. 
    - Should implement Request for index where custom filters are used (Advanced Filter). Request should extend BaseFormRequest that handles camelCase to snake_case transformation. We should use camelCase for frontend input.

2. Requests, validation, filtering 
    - Should implement custom validator for advanced filter. Should consider columns data type to validate allowed operators, and considering type & operator to validate filter value. 
    - Should implement error handling for indexQuery in case of invalid filter values or operators.

3. Tests
    - Every controller should have a test defined.
    - We use custom authentication trait since we're using JWT and tenancy.
    - We should use `Tenant::bypassTenantScope()` when creating/checking model inside tenant scope.
    - We should test all filters for index method of a controller.
    - We should use "attributes" to annotate tests, and what is covered (not docBlock).
    - We should use documented mock for Saloon from Saloon Laravel plugin where needed.

## Missing Resources and Tests

### Controllers Missing Resources

_All required resources for the listed controllers are present and implemented correctly._

### Controllers Missing Tests
- [ ] FeedController - tests are marked as skipped, need implementation
- [ ] CertificateController - tests are marked as skipped, need implementation
- [ ] MessageController - tests are marked as skipped, need implementation
- [ ] ExchangeController - tests are marked as skipped, need implementation
- [ ] ExchangeRateController - needs test implementation
- [ ] ProjectController - needs test implementation
- [ ] TaskController - needs test implementation
- [ ] TaskStatusController - needs test implementation
- [ ] ProjectStatusController - needs test implementation
- [ ] SkillController - needs test implementation
- [ ] SkillCategoryController - needs test implementation
- [ ] ContractorController - needs test implementation
- [ ] TagController - needs test implementation
- [ ] CountryController - needs test implementation
- [ ] UserIdentityController - needs test implementation
- [ ] UserSettingsController - needs test implementation
- [ ] UserProfileImageController - needs test implementation
- [ ] NotificationController - needs test implementation
- [ ] UserTableSettingController - needs test implementation
- [ ] NotificationSettingController - needs test implementation
- [ ] RoleController - needs test implementation

### Controllers Missing Request Classes
- [ ] FeedController - needs UpdateFeedRequest
- [ ] CertificateController - needs UpdateCertificateRequest
- [ ] MessageController - needs UpdateMessageRequest
- [ ] ExchangeController - needs StoreExchangeRequest and UpdateExchangeRequest
- [ ] ExchangeRateController - needs StoreExchangeRateRequest and UpdateExchangeRateRequest
- [ ] ProjectController - needs SearchProjectRequest
- [ ] TaskController - needs SearchTaskRequest
- [ ] TaskStatusController - needs UpdateTaskStatusRequest
- [ ] ProjectStatusController - needs UpdateProjectStatusRequest
- [ ] SkillController - needs UpdateSkillRequest
- [ ] SkillCategoryController - needs UpdateSkillCategoryRequest
- [ ] ContractorController - needs UpdateContractorRequest
- [ ] TagController - needs StoreTagRequest and UpdateTagRequest
- [ ] CountryController - needs StoreCountryRequest and UpdateCountryRequest

### Controllers Missing Index Query
- [ ] FeedController - needs FeedIndexQuery for filtering
- [ ] CertificateController - needs CertificateIndexQuery for filtering
- [ ] MessageController - needs MessageIndexQuery for filtering
- [ ] ExchangeController - needs ExchangeIndexQuery for filtering
- [ ] ExchangeRateController - needs ExchangeRateIndexQuery for filtering
- [ ] ProjectController - needs ProjectIndexQuery for filtering
- [ ] TaskController - needs TaskIndexQuery for filtering
- [ ] TaskStatusController - needs TaskStatusIndexQuery for filtering
- [ ] ProjectStatusController - needs ProjectStatusIndexQuery for filtering
- [ ] SkillController - needs SkillIndexQuery for filtering
- [ ] SkillCategoryController - needs SkillCategoryIndexQuery for filtering
- [ ] ContractorController - needs ContractorIndexQuery for filtering
- [ ] TagController - needs TagIndexQuery for filtering
- [ ] CountryController - needs CountryIndexQuery for filtering


