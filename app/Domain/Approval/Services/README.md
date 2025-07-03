# Approval Services

## ApprovalResolutionService

The `ApprovalResolutionService` is responsible for resolving approvers based on different approval step configurations. It takes an `ApprovalStepApprover` configuration and returns a collection of `User` objects who should approve a given expense.

### Approver Types

The service supports three types of approvers:

#### 1. USER (Specific User)
- **Field**: `approver_value` contains the user ID
- **Logic**: Finds and returns the specific user
- **Fallbacks**: Returns empty collection if user not found or inactive

#### 2. UNIT_ROLE (Organization Unit Role)
- **Field**: `approver_value` contains the role name (e.g., "unit-owner", "unit-deputy")
- **Logic**: 
  - Gets the expense creator's primary organizational unit
  - Resolves target unit (primary unit, specific unit via `organization_unit_id`, or parent unit)
  - Finds all users with the specified role in the target unit
- **Special Values**: 
  - `"PARENT_UNIT"` - looks for approvers in the parent unit
- **Fallbacks**: Returns empty collection if no primary unit, no target unit, or no users with role

#### 3. SYSTEM_PERMISSION (System Permission)
- **Field**: `approver_value` contains the permission name
- **Logic**: Finds all users who have the specified system permission
- **Dependencies**: Requires Spatie Laravel Permission package

### Key Methods

```php
// Main resolution method
public function resolveApprovers(ApprovalStepApprover $stepApprover, Expense $expense): Collection

// Check if user has specific permission
public function userHasSystemPermission(User $user, string $permission): bool

// Get all possible approvers for an expense (placeholder for future implementation)
public function getAllAvailableApprovers(Expense $expense): Collection
```

### Error Handling

The service includes comprehensive logging for troubleshooting:
- Missing configuration values
- Users not found
- Organizational unit issues
- Permission system problems

All methods gracefully return empty collections when approvers cannot be resolved.

### Dependencies

- **User Model**: `App\Domain\Auth\Models\User`
- **Expense Model**: `App\Domain\Expense\Models\Expense`
- **ApprovalStepApprover Model**: `App\Domain\Approval\Models\ApprovalStepApprover`
- **ApproverType Enum**: `App\Domain\Approval\Enums\ApproverType`
- **Laravel Collections**: `Illuminate\Database\Eloquent\Collection`
- **Logging**: `Illuminate\Support\Facades\Log`

### Integration Points

This service is designed to be used by:
- Workflow execution engines
- Approval decision processors  
- Administrative interfaces for approver preview
- Notification systems

### Future Enhancements

- Support for delegation chains
- Caching of resolved approvers
- Integration with organizational hierarchy changes
- Support for time-based approver assignments 
