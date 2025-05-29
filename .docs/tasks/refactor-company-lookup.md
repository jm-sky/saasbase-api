# Refactor Company Lookup

I need to refactor our company lookup services to improve code consistency and maintainability. We have three services that use Saloon for API integration:

1. GusLookup (REGON) - rename to RegonLookup ✅
2. ViesLookup (VIES) - needs refactoring ⚠️
3. CompanyLookup (MF) - rename to MfLookup ✅

## Plan

Please implement the following changes:

1. Service Renaming:
   - Rename GusLookup to RegonLookup: ✅
     * [x] Rename directory from `app/Services/GusLookup` to `app/Services/RegonLookup`
     * [x] Update all namespace references from `App\Services\GusLookup` to `App\Services\RegonLookup`
     * [x] Update all class names containing "Gus" to "Regon" where appropriate
     * [x] Update any configuration files referencing the old name
     * [x] Update any service provider bindings
   
   - Rename CompanyLookup to MfLookup: ✅
     * [x] Rename directory from `app/Services/CompanyLookup` to `app/Services/MfLookup`
     * [x] Update all namespace references from `App\Services\CompanyLookup` to `App\Services\MfLookup`
     * [x] Update all class names containing "Company" to "Mf" where appropriate
     * [x] Update any configuration files referencing the old name
     * [x] Update any service provider bindings

2. For ViesLookup service: ⚠️
   - Create a new DTO class `ViesCheckResultDTO` in `app/Services/ViesLookup/DTOs/` with the following fields: ✅
     * [x] valid (bool)
     * [x] countryCode (string|null)
     * [x] vatNumber (string|null)
     * [x] requestDate (string|null)
     * [x] name (string|null)
     * [x] address (string|null)
   - [x] Modify `CheckVatRequest` to implement `createDtoFromResponse` method that transforms SOAP response into the DTO
   - [x] Add proper error handling for SOAP responses
   - [x] Consider creating a base request class for common SOAP functionality

3. For MfLookup service (formerly CompanyLookup): ✅
   - Create a new DTO class `MfSearchResultDTO` in `app/Services/MfLookup/DTOs/` with the following fields: ✅
     * [x] nip (string)
     * [x] name (string)
     * [x] status (string)
     * [x] regon (string|null)
     * [x] address (string|null)
     * [x] Add other relevant fields from the API response
   - [x] Modify `SearchByNipRequest` to implement `createDtoFromResponse` method
   - [x] Create a base request class `BaseMfRequest` with common functionality:
     * [x] Date handling method
     * [x] Error handling
     * [x] Common headers if needed
   - [x] Update `SearchByNipRequest` to extend the base class

4. General improvements: ✅
   - [x] Add proper type hints and return types
   - [x] Add PHPDoc blocks for better documentation
   - [x] Implement consistent error handling across all services
   - [x] Ensure all DTOs are immutable (readonly properties)
   - [x] Add validation for required fields
   - [x] Update any documentation referencing old service names
   - [x] Update any tests to use new service names

Remaining Tasks:
1. ViesLookup Service:
   - [x] Create `ViesCheckResultDTO` class
   - [x] Implement SOAP response handling in `CheckVatRequest`
   - [x] Create base SOAP request class
   - [x] Add proper error handling for SOAP responses
   - [x] Update tests to use Saloon's mocking instead of Mockery

2. Tests:
   - [x] Update all tests to use Saloon's built-in mocking instead of Mockery
   - [x] Add tests for ViesLookup service
   - [x] Add tests for error handling scenarios

3. Documentation:
   - [x] Update API documentation to reflect new service names
   - [x] Add examples of using the new DTOs
   - [x] Document error handling patterns

The refactoring should follow these principles:
- Keep the code DRY (Don't Repeat Yourself)
- Follow SOLID principles
- Maintain backward compatibility
- Ensure type safety
- Make the code easily testable
- Follow Laravel and PHP best practices
- Use consistent naming conventions across all services

Note: The renaming from GusLookup to RegonLookup and CompanyLookup to MfLookup makes the service names more consistent with their actual purposes (REGON database and Ministry of Finance database respectively).

All tasks have been completed! The refactoring is now finished with:
1. All services renamed and properly structured
2. New DTOs created and implemented
3. Error handling improved
4. Tests updated to use Saloon's mocking
5. Documentation updated with examples and best practices

Let me know if you need any clarification or have questions about the implementation.


## Manual checks
- VIES
  - [x] Check command
  - [ ] Check service
  - [ ] Working
- REGON
  - [x] Check command
  - [ ] Check service
  - [ ] Working
- MF
  - [x] Check command
  - [ ] Check service
  - [ ] Working
