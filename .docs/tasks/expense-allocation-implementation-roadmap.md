# Expense Allocation & Approval System - Detailed Implementation Roadmap

## Overview

This document provides a step-by-step implementation plan for the expense allocation and approval system. Each task is designed to be **small, focused, and testable** to ensure smooth AI-guided implementation.

## Naming Convention

To avoid conflicts and improve organization, we use prefixed naming:

- **`allocation_*`** - Tables and models for allocation dimensions and core allocation functionality
- **`approval_*`** - Tables and models for approval workflows and decisions  
- **`expense_*`** - Tables and models directly related to expense processing
- **Standard names** - Enums, services, and actions follow descriptive naming without prefixes

Examples:
- ✅ `allocation_transaction_types` table → `AllocationTransactionType` model
- ✅ `approval_workflows` table → `ApprovalWorkflow` model  
- ✅ `expense_allocations` table → `ExpenseAllocation` model

## Phase 1: Foundation - Database & Global Dimensions

### Task 1.1: Create Global Dimension Tables & Models (High Priority)
**Estimated Time**: 2-3 hours  
**Dependencies**: None

#### Subtasks:
1. **1.1.1**: Create migration for `allocation_transaction_types` table
2. **1.1.2**: Create migration for `allocation_cost_types` table  
3. **1.1.3**: Create migration for `allocation_related_transaction_categories` table
4. **1.1.4**: Create `AllocationTransactionType` model with `IsGlobalOrBelongsToTenant` trait
5. **1.1.5**: Create `AllocationCostType` model with `IsGlobalOrBelongsToTenant` trait
6. **1.1.6**: Create `AllocationRelatedTransactionCategory` model with `IsGlobalOrBelongsToTenant` trait
7. **1.1.7**: Test models work correctly with global/tenant scope

#### Acceptance Criteria:
- [ ] All three tables created with correct schema
- [ ] Models correctly use `IsGlobalOrBelongsToTenant` trait
- [ ] Can create both global (tenant_id = null) and tenant-specific records
- [ ] Basic CRUD operations work for all three models

---

### Task 1.2: Create Remaining Global Dimension Tables & Models (High Priority)
**Estimated Time**: 3-4 hours  
**Dependencies**: Task 1.1

#### Subtasks:
1. **1.2.1**: Create migration for `allocation_locations` table
2. **1.2.2**: Create migration for `allocation_equipment_types` table
3. **1.2.3**: Create migration for `allocation_organization_units` table (with parent_id for hierarchy)
4. **1.2.4**: Create migration for `allocation_revenue_types` table
5. **1.2.5**: Create migration for `allocation_product_categories` table
6. **1.2.6**: Create migration for `allocation_contract_types` table
7. **1.2.7**: Create corresponding models: `AllocationLocation`, `AllocationEquipmentType`, `AllocationOrganizationUnit`, `AllocationRevenueType`, `AllocationProductCategory`, `AllocationContractType`
8. **1.2.8**: Test hierarchical relationships work for `AllocationOrganizationUnit`

#### Acceptance Criteria:
- [ ] All dimension tables created successfully
- [ ] All models work with global/tenant scoping
- [ ] AllocationOrganizationUnit parent/child relationships work correctly
- [ ] AllocationLocation model includes address field correctly

---

### Task 1.3: Create Global Dimension Seeders (Medium Priority)
**Estimated Time**: 2 hours  
**Dependencies**: Task 1.1, 1.2

#### Subtasks:
1. **1.3.1**: Create `DefaultAllocationTransactionTypesSeeder` with Polish transaction types
2. **1.3.2**: Create `DefaultAllocationCostTypesSeeder` with accounting cost categories
3. **1.3.3**: Create `DefaultAllocationRelatedTransactionCategoriesSeeder`
4. **1.3.4**: Create `DefaultAllocationLocationsSeeder` with common location types
5. **1.3.5**: Create `DefaultAllocationEquipmentTypesSeeder` 
6. **1.3.6**: Create `DefaultAllocationOrganizationUnitsSeeder`
7. **1.3.7**: Run all seeders and verify global data exists

#### Acceptance Criteria:
- [ ] All seeders create global records (tenant_id = null)
- [ ] Polish transaction types exactly match specification
- [ ] Seeders are idempotent (can run multiple times safely)
- [ ] Default data is useful for real businesses

---

## Phase 2: Organizational Structure & Permissions

### Task 2.1: Create Organization Unit Membership System (High Priority)
**Estimated Time**: 3-4 hours  
**Dependencies**: Task 1.2

#### Subtasks:
1. **2.1.1**: Create migration for `allocation_organization_unit_memberships` table
2. **2.1.2**: Create `UnitRoleLevel` enum with hierarchy logic
3. **2.1.3**: Create `AllocationOrganizationUnitMembership` model
4. **2.1.4**: Add membership relationships to `AllocationOrganizationUnit` model
5. **2.1.5**: Add membership relationships to `User` model
6. **2.1.6**: Create `AllocationOrganizationUnitService` with core methods
7. **2.1.7**: Test membership creation and role hierarchy

#### Acceptance Criteria:
- [ ] Users can be assigned to multiple units with different roles
- [ ] Role hierarchy works (unit-owner > unit-deputy > unit-member)
- [ ] Primary unit detection works correctly
- [ ] Valid date ranges for memberships work
- [ ] Service methods return correct users for unit roles

---

### Task 2.2: Create Dimension Visibility Configuration (Medium Priority)  
**Estimated Time**: 2-3 hours
**Dependencies**: Task 1.1, 1.2

#### Subtasks:
1. **2.2.1**: Create migration for `tenant_dimension_configurations` table
2. **2.2.2**: Create `TenantDimensionConfiguration` model
3. **2.2.3**: Update `AllocationDimensionType` enum with visibility methods
4. **2.2.4**: Create `DimensionVisibilityService`
5. **2.2.5**: Create seeder for default tenant configurations
6. **2.2.6**: Test RTR is always visible, others are configurable

#### Acceptance Criteria:
- [ ] RTR dimension is always visible regardless of configuration
- [ ] Other dimensions can be enabled/disabled per tenant
- [ ] Service returns dimensions in correct display order
- [ ] Default configurations are reasonable for new tenants

---

## Phase 3: Core Allocation System

### Task 3.1: Create Expense Allocation Tables & Models (High Priority)
**Estimated Time**: 3-4 hours  
**Dependencies**: Task 2.2

#### Subtasks:
1. **3.1.1**: Create migration for `expense_allocations` table
2. **3.1.2**: Create migration for `allocation_dimensions` table  
3. **3.1.3**: Create `ExpenseAllocationStatus` enum
4. **3.1.4**: Create `ExpenseAllocation` model with BigDecimal amount
5. **3.1.5**: Create `AllocationDimension` model with morph relationship
6. **3.1.6**: Update existing `Expense` model with allocation relationships
7. **3.1.7**: Test allocation creation with multiple dimensions

#### Acceptance Criteria:
- [ ] Can allocate expense amounts across different dimensions
- [ ] Morphic relationships work for all dimension types
- [ ] BigDecimal amounts work correctly for precision
- [ ] Allocation status changes work properly
- [ ] Expense totals calculate correctly

---

### Task 3.2: Create Allocation Business Logic (High Priority)
**Estimated Time**: 3-4 hours
**Dependencies**: Task 3.1

#### Subtasks:
1. **3.2.1**: Create `AllocateExpenseAction` with dimension validation
2. **3.2.2**: Implement allocation validation (amounts must sum to total)
3. **3.2.3**: Create allocation dimension availability check
4. **3.2.4**: Update expense status flow for allocations
5. **3.2.5**: Add transaction safety for allocation operations
6. **3.2.6**: Test complex allocation scenarios

#### Acceptance Criteria:
- [ ] Cannot allocate more than expense total amount
- [ ] Cannot allocate to disabled dimensions
- [ ] Expense status updates correctly through allocation flow
- [ ] All allocation operations are transactional
- [ ] Clear error messages for validation failures

---

## Phase 4: Approval Workflow Foundation

### Task 4.1: Create Approval Workflow Tables & Models (High Priority)
**Estimated Time**: 4-5 hours
**Dependencies**: Task 2.1

#### Subtasks:
1. **4.1.1**: Create migration for `approval_workflows` table
2. **4.1.2**: Create migration for `approval_workflow_steps` table
3. **4.1.3**: Create migration for `approval_step_approvers` table (updated schema)
4. **4.1.4**: Create migration for `approval_expense_executions` table
5. **4.1.5**: Create migration for `approval_expense_decisions` table
6. **4.1.6**: Create all corresponding models: `ApprovalWorkflow`, `ApprovalWorkflowStep`, `ApprovalStepApprover`, `ApprovalExpenseExecution`, `ApprovalExpenseDecision`
7. **4.1.7**: Create necessary enums (`ApprovalExecutionStatus`, `ApprovalDecision`, `ApproverType`)
8. **4.1.8**: Test workflow creation and step relationships

#### Acceptance Criteria:
- [ ] Can create workflows with multiple ordered steps
- [ ] Step approvers support user, unit_role, and system_permission types
- [ ] Approval executions track current step correctly
- [ ] All relationships between models work properly

---

### Task 4.2: Create Approval Resolution Logic (High Priority)
**Estimated Time**: 3-4 hours
**Dependencies**: Task 4.1

#### Subtasks:
1. **4.2.1**: Create `ApprovalResolutionService` for finding approvers
2. **4.2.2**: Implement user-specific approver resolution
3. **4.2.3**: Implement unit-role approver resolution with hierarchy
4. **4.2.4**: Implement system-permission approver resolution
5. **4.2.5**: Handle special cases (PARENT_UNIT, no primary unit, etc.)
6. **4.2.6**: Test all approver resolution scenarios

#### Acceptance Criteria:
- [ ] Correctly finds specific users for approval
- [ ] Correctly finds unit role holders (owners, deputies, etc.)
- [ ] Handles parent unit approvals correctly
- [ ] Falls back gracefully when no approvers found
- [ ] Returns empty collection for invalid configurations

---

## Phase 5: Workflow Execution Engine

### Task 5.1: Create Workflow Matching Service (High Priority)
**Estimated Time**: 2-3 hours
**Dependencies**: Task 4.1

#### Subtasks:
1. **5.1.1**: Create `WorkflowMatchingService` for finding workflows
2. **5.1.2**: Implement amount-based matching logic
3. **5.1.3**: Implement allocation dimension-based conditions
4. **5.1.4**: Implement priority-based workflow selection
5. **5.1.5**: Test workflow matching with various expense scenarios

#### Acceptance Criteria:
- [ ] Matches workflows based on expense amount ranges
- [ ] Matches workflows based on allocation dimensions
- [ ] Selects highest priority workflow when multiple match
- [ ] Returns null when no workflows match (auto-approve)

---

### Task 5.2: Create Approval Execution Engine (High Priority)
**Estimated Time**: 4-5 hours
**Dependencies**: Task 5.1, 4.2

#### Subtasks:
1. **5.2.1**: Create `StartApprovalWorkflowAction`
2. **5.2.2**: Create `ProcessApprovalDecisionAction`
3. **5.2.3**: Implement step progression logic
4. **5.2.4**: Implement parallel vs sequential approver handling
5. **5.2.5**: Update expense status based on approval results
6. **5.2.6**: Test complete approval workflows end-to-end

#### Acceptance Criteria:
- [ ] Workflows start automatically after allocation
- [ ] Steps progress correctly after decisions
- [ ] Parallel approvals require correct number of approvers
- [ ] Sequential approvals wait for each step
- [ ] Expense status updates correctly at workflow completion

---

## Phase 6: API & Integration

### Task 6.1: Create Allocation API Endpoints (Medium Priority)
**Estimated Time**: 3-4 hours
**Dependencies**: Task 3.2

#### Subtasks:
1. **6.1.1**: Create `ExpenseAllocationController`
2. **6.1.2**: Implement allocation creation endpoint
3. **6.1.3**: Implement allocation viewing endpoint
4. **6.1.4**: Create allocation validation rules
5. **6.1.5**: Create API resources for clean responses
6. **6.1.6**: Test API endpoints with Postman/tests

#### Acceptance Criteria:
- [ ] Can create allocations via API
- [ ] Can view expense allocations via API  
- [ ] Proper validation error responses
- [ ] API responses follow camelCase convention
- [ ] Proper authorization checks

---

### Task 6.2: Create Approval API Endpoints (Medium Priority)
**Estimated Time**: 3-4 hours
**Dependencies**: Task 5.2

#### Subtasks:
1. **6.2.1**: Create `ExpenseApprovalController`
2. **6.2.2**: Implement approval decision endpoints
3. **6.2.3**: Create pending approvals list endpoint
4. **6.2.4**: Create approval history endpoint
5. **6.2.5**: Implement proper authorization for approvals
6. **6.2.6**: Test approval workflows via API

#### Acceptance Criteria:
- [ ] Users can approve/reject via API
- [ ] Users can view their pending approvals
- [ ] Users can view approval history
- [ ] Proper authorization prevents unauthorized approvals
- [ ] Clear error messages for invalid actions

---

## Phase 7: Advanced Features

### Task 7.1: Create Allocation Templates (Low Priority)
**Estimated Time**: 2-3 hours
**Dependencies**: Task 3.2

#### Subtasks:
1. **7.1.1**: Create migration for `allocation_templates` table
2. **7.1.2**: Create `AllocationTemplate` model
3. **7.1.3**: Create `ApplyAllocationTemplateAction`
4. **7.1.4**: Create template management API endpoints
5. **7.1.5**: Test template application

#### Acceptance Criteria:
- [ ] Can save allocation patterns as templates
- [ ] Can apply templates to new expenses
- [ ] Templates respect current dimension visibility settings
- [ ] Template data is properly validated

---

### Task 7.2: Create Approval Delegation System (Low Priority)
**Estimated Time**: 3-4 hours
**Dependencies**: Task 5.2

#### Subtasks:
1. **7.2.1**: Create migration for `approval_delegations` table
2. **7.2.2**: Create `ApprovalDelegation` model
3. **7.2.3**: Update approval resolution to check for delegations
4. **7.2.4**: Create delegation management endpoints
5. **7.2.5**: Test delegation workflows

#### Acceptance Criteria:
- [ ] Users can delegate approval authority with date ranges
- [ ] Delegated approvals show proper attribution
- [ ] Delegations respect organizational hierarchy
- [ ] Delegation validity dates work correctly

---

## Implementation Strategy Recommendations

### Start With:
1. **Task 1.1** - Global dimensions foundation
2. **Task 1.2** - Complete dimension system  
3. **Task 2.1** - Organizational structure

### Then Move To:
4. **Task 3.1 & 3.2** - Core allocation system
5. **Task 4.1 & 4.2** - Approval foundation

### Finally:
6. **Task 5.1 & 5.2** - Workflow execution
7. **Task 6.1 & 6.2** - API integration

### Benefits of This Approach:
- ✅ **Small, testable tasks** - Each task can be completed and verified independently
- ✅ **Clear dependencies** - Know exactly what needs to be done before each task
- ✅ **Incremental progress** - System grows in working increments
- ✅ **Easy debugging** - Problems are isolated to specific components
- ✅ **Flexible prioritization** - Can skip low-priority features initially

Would you like to start with **Task 1.1** (Global Dimension Tables & Models)? 
