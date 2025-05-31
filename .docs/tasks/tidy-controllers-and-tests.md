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


