# Expense Allocation and Approval Workflows - Comprehensive Specification

## Overview

This specification defines a comprehensive expense allocation and approval system for SaaSBase that integrates with the existing Expense domain and adds a new Approval domain for workflow management.

## Business Requirements

### Core Features
- **Multi-dimensional expense allocation** with 11 predefined business dimensions
- **Conditional approval workflows** based on amount, allocation dimensions, and tenant configuration  
- **Sequential and parallel approval steps** with flexible approver assignment
- **Template-based allocation** for common expense patterns
- **Approval delegation** and notification system
- **Complete audit trail** of all approval decisions
- **Automatic OCR integration** for seamless expense processing

### Business Dimensions

| Code | Name (PL) | Name (EN) | Description | Scope | Visibility |
|------|-----------|-----------|-------------|-------|------------|
| HA | Pracownicy | Employees | Employee/worker assignments | **Global + Tenant** | Configurable |
| LO | Lokalizacja | Location | Office/site locations | **Global + Tenant** | Configurable |
| PD | Produkty | Products | Product allocations | **Global + Tenant** | Configurable |
| PR | Projekt | Project | Project assignments | **Global + Tenant** | Configurable |
| RS | Rodzaj przychodów | Revenue Type | Revenue categorization | **Global + Tenant** | Configurable |
| RTR | Rodzaj transakcji | Transaction Type | Transaction categorization | **Global + Tenant** | **Always Visible** |
| RY | Rodzaj kosztów | Cost Type | Cost categorization | **Global + Tenant** | Configurable |
| ST | Struktura/Działy | Structure/Departments | Organizational structure | **Global + Tenant** | Configurable |
| TP | Transakcje powiązane | Related Transactions | Related transaction links | **Global + Tenant** | Configurable |
| UM | Umowy | Contracts | Contract associations | **Global + Tenant** | Configurable |
| UR | Urządzenia | Equipment/Devices | Equipment/device allocations | **Global + Tenant** | Configurable |

### Dimension Visibility Configuration

**RTR (Transaction Type)** is always visible as it's essential for accounting compliance. All other dimensions can be **enabled/disabled per tenant**:

- **Always Visible**: RTR (required for accounting)
- **Configurable**: All other dimensions (HA, LO, PD, PR, RS, RY, ST, TP, UM, UR)

Each tenant can configure which dimensions are available in their expense allocation interface.

### Global Dimension Models

**ALL dimensions** support both global and tenant-specific values:
- **Global values**: Default values shared across all tenants (with `tenant_id = NULL`)
- **Tenant values**: Custom values created by specific tenants (with their `tenant_id`)

### Global vs Tenant Value Display Strategy

Two possible approaches for showing dimension values:
1. **Show both global and tenant-specific values** ✅ **(Current Implementation)**
2. **Show only tenant-specific if available, fallback to global** *(Future option)*

We're implementing **approach #1** where tenants see both:
- All available global default values
- Their own custom tenant-specific values

This gives tenants maximum flexibility to use standardized global values or create their own.

### Examples of Global Default Values

**Transaction Types (RTR)** - Always visible:
- `10_zakup_materialow_produkcyjnych` - Zakup materiałów produkcyjnych
- `10_zakup_towarow` - Zakup towarów  
- `10_zakup_towarow_odwrotne_obciazenie` - Zakup towarów - odwrotne obciążenie

**Cost Types (RY)**:
- `01_koszty_materialow` - Koszty materiałów
- `02_koszty_pracy` - Koszty pracy
- `03_koszty_ogolne` - Koszty ogólne

**Related Transaction Categories (TP)**:
- `import_transaction` - Transakcja importowa
- `export_transaction` - Transakcja eksportowa
- `internal_transfer` - Transfer wewnętrzny

**Locations (LO)**:
- `headquarters` - Siedziba główna
- `warehouse` - Magazyn
- `retail_store` - Sklep detaliczny
- `home_office` - Biuro domowe

**Equipment Types (UR)**:
- `computer_hardware` - Sprzęt komputerowy
- `office_furniture` - Meble biurowe
- `vehicles` - Pojazdy
- `production_equipment` - Sprzęt produkcyjny

**Organization Structure (ST)**:
- `management` - Zarząd
- `administration` - Administracja
- `sales` - Sprzedaż
- `production` - Produkcja
- `it_department` - Dział IT

## Architecture Overview

```
app/Domain/Expense/         (extend existing)
├── Models/
│   ├── ExpenseAllocation.php
│   ├── AllocationDimension.php
│   └── AllocationTemplate.php
├── Enums/
│   ├── ExpenseAllocationStatus.php
│   └── AllocationDimensionType.php
├── Actions/
│   ├── AllocateExpenseAction.php
│   └── ApplyAllocationTemplateAction.php
└── Services/
    └── ExpenseAllocationService.php

app/Domain/Approval/        (new domain)
├── Models/
│   ├── ApprovalWorkflow.php
│   ├── WorkflowStep.php
│   ├── StepApprover.php
│   ├── ExpenseApprovalExecution.php
│   └── ExpenseApprovalDecision.php
├── Enums/
│   ├── ApprovalExecutionStatus.php
│   ├── ApprovalDecision.php
│   └── ApproverType.php
├── Services/
│   ├── WorkflowMatchingService.php
│   ├── ApprovalExecutionService.php
│   └── ApprovalNotificationService.php
└── Actions/
    ├── StartApprovalWorkflowAction.php
    ├── ProcessApprovalDecisionAction.php
    └── DelegateApprovalAction.php
```

## Database Schema

### Expense Domain Extensions

#### expense_allocations
```sql
CREATE TABLE expense_allocations (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NOT NULL,
    expense_id CHAR(26) NOT NULL,
    amount DECIMAL(19,4) NOT NULL,
    note TEXT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE CASCADE,
    INDEX idx_expense_allocations_expense_id (expense_id),
    INDEX idx_expense_allocations_tenant_id (tenant_id)
);
```

#### allocation_dimensions
```sql
CREATE TABLE allocation_dimensions (
    id CHAR(26) PRIMARY KEY,
    allocation_id CHAR(26) NOT NULL,
    dimension_type VARCHAR(10) NOT NULL,
    dimension_id CHAR(26) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (allocation_id) REFERENCES expense_allocations(id) ON DELETE CASCADE,
    INDEX idx_allocation_dimensions_allocation_id (allocation_id),
    INDEX idx_allocation_dimensions_type_id (dimension_type, dimension_id),
    UNIQUE KEY uk_allocation_dimension_type (allocation_id, dimension_type, dimension_id)
);
```

#### allocation_templates
```sql
CREATE TABLE allocation_templates (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    template_data JSON NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_by CHAR(26) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_allocation_templates_tenant_id (tenant_id)
);
```

### Approval Domain

#### approval_workflows
```sql
CREATE TABLE approval_workflows (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    match_amount_min DECIMAL(19,4) NULL,
    match_amount_max DECIMAL(19,4) NULL,
    match_conditions JSON NULL,
    priority INTEGER NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_by CHAR(26) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_approval_workflows_tenant_id (tenant_id),
    INDEX idx_approval_workflows_priority (priority)
);
```

#### workflow_steps
```sql
CREATE TABLE workflow_steps (
    id CHAR(26) PRIMARY KEY,
    workflow_id CHAR(26) NOT NULL,
    step_order INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    require_all_approvers BOOLEAN NOT NULL DEFAULT FALSE,
    min_approvers INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (workflow_id) REFERENCES approval_workflows(id) ON DELETE CASCADE,
    INDEX idx_workflow_steps_workflow_id (workflow_id),
    UNIQUE KEY uk_workflow_step_order (workflow_id, step_order)
);
```

#### step_approvers
```sql
CREATE TABLE step_approvers (
    id CHAR(26) PRIMARY KEY,
    step_id CHAR(26) NOT NULL,
    approver_type ENUM('user', 'unit_role', 'system_permission') NOT NULL,
    approver_value VARCHAR(255) NOT NULL,
    organization_unit_id CHAR(26) NULL,
    can_delegate BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (step_id) REFERENCES workflow_steps(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_unit_id) REFERENCES organization_units(id) ON DELETE CASCADE,
    INDEX idx_step_approvers_step_id (step_id),
    INDEX idx_step_approvers_unit_id (organization_unit_id),
    UNIQUE KEY uk_step_approver (step_id, approver_type, approver_value, organization_unit_id)
);
```

#### expense_approval_executions
```sql
CREATE TABLE expense_approval_executions (
    id CHAR(26) PRIMARY KEY,
    expense_id CHAR(26) NOT NULL,
    workflow_id CHAR(26) NOT NULL,
    current_step_id CHAR(26) NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    started_at TIMESTAMP NOT NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE CASCADE,
    FOREIGN KEY (workflow_id) REFERENCES approval_workflows(id) ON DELETE CASCADE,
    FOREIGN KEY (current_step_id) REFERENCES workflow_steps(id) ON DELETE SET NULL,
    INDEX idx_approval_executions_expense_id (expense_id),
    INDEX idx_approval_executions_status (status)
);
```

#### expense_approval_decisions
```sql
CREATE TABLE expense_approval_decisions (
    id CHAR(26) PRIMARY KEY,
    execution_id CHAR(26) NOT NULL,
    step_id CHAR(26) NOT NULL,
    approver_id CHAR(26) NOT NULL,
    decision ENUM('approved', 'rejected') NOT NULL,
    reason TEXT NULL,
    decided_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (execution_id) REFERENCES expense_approval_executions(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES workflow_steps(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_approval_decisions_execution_id (execution_id),
    INDEX idx_approval_decisions_approver_id (approver_id),
    UNIQUE KEY uk_approval_decision (execution_id, step_id, approver_id)
);
```

#### approval_delegations
```sql
CREATE TABLE approval_delegations (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NOT NULL,
    delegator_id CHAR(26) NOT NULL,
    delegate_id CHAR(26) NOT NULL,
    valid_from TIMESTAMP NOT NULL,
    valid_until TIMESTAMP NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    reason TEXT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (delegator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (delegate_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_approval_delegations_delegator_id (delegator_id),
    INDEX idx_approval_delegations_delegate_id (delegate_id)
);
```

### Global Dimension Tables

#### transaction_types (RTR)
```sql
CREATE TABLE transaction_types (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NULL,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_transaction_types_tenant_id (tenant_id),
    INDEX idx_transaction_types_code (code),
    UNIQUE KEY uk_transaction_type_code_tenant (code, tenant_id)
);
```

#### cost_types (RY)
```sql
CREATE TABLE cost_types (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NULL,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_cost_types_tenant_id (tenant_id),
    INDEX idx_cost_types_code (code),
    UNIQUE KEY uk_cost_type_code_tenant (code, tenant_id)
);
```

#### related_transaction_categories (TP)
```sql
CREATE TABLE related_transaction_categories (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NULL,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_related_transaction_categories_tenant_id (tenant_id),
    INDEX idx_related_transaction_categories_code (code),
    UNIQUE KEY uk_related_transaction_category_code_tenant (code, tenant_id)
);
```

#### locations (LO)
```sql
CREATE TABLE locations (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NULL,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    address TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_locations_tenant_id (tenant_id),
    INDEX idx_locations_code (code),
    UNIQUE KEY uk_location_code_tenant (code, tenant_id)
);
```

#### equipment_types (UR)
```sql
CREATE TABLE equipment_types (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NULL,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_equipment_types_tenant_id (tenant_id),
    INDEX idx_equipment_types_code (code),
    UNIQUE KEY uk_equipment_type_code_tenant (code, tenant_id)
);
```

#### organization_units (ST)
```sql
CREATE TABLE organization_units (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NULL,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    parent_id CHAR(26) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES organization_units(id) ON DELETE SET NULL,
    INDEX idx_organization_units_tenant_id (tenant_id),
    INDEX idx_organization_units_code (code),
    INDEX idx_organization_units_parent_id (parent_id),
    UNIQUE KEY uk_organization_unit_code_tenant (code, tenant_id)
);
```

#### organization_unit_memberships
```sql
CREATE TABLE organization_unit_memberships (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NOT NULL,
    user_id CHAR(26) NOT NULL,
    organization_unit_id CHAR(26) NOT NULL,
    role_level ENUM('unit-member', 'unit-deputy', 'unit-owner', 'unit-admin') NOT NULL DEFAULT 'unit-member',
    is_primary BOOLEAN NOT NULL DEFAULT FALSE,
    valid_from TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    valid_until TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_unit_id) REFERENCES organization_units(id) ON DELETE CASCADE,
    INDEX idx_org_memberships_user_id (user_id),
    INDEX idx_org_memberships_unit_id (organization_unit_id),
    INDEX idx_org_memberships_tenant_id (tenant_id),
    UNIQUE KEY uk_user_unit_membership (user_id, organization_unit_id)
);
```

#### revenue_types (RS)
```sql
CREATE TABLE revenue_types (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NULL,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_revenue_types_tenant_id (tenant_id),
    INDEX idx_revenue_types_code (code),
    UNIQUE KEY uk_revenue_type_code_tenant (code, tenant_id)
);
```

#### product_categories (PD)
```sql
CREATE TABLE product_categories (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NULL,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_product_categories_tenant_id (tenant_id),
    INDEX idx_product_categories_code (code),
    UNIQUE KEY uk_product_category_code_tenant (code, tenant_id)
);
```

#### contract_types (UM)
```sql
CREATE TABLE contract_types (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NULL,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_contract_types_tenant_id (tenant_id),
    INDEX idx_contract_types_code (code),
    UNIQUE KEY uk_contract_type_code_tenant (code, tenant_id)
);
```

### Tenant Configuration Tables

#### tenant_dimension_configurations
```sql
CREATE TABLE tenant_dimension_configurations (
    id CHAR(26) PRIMARY KEY,
    tenant_id CHAR(26) NOT NULL,
    dimension_type VARCHAR(10) NOT NULL,
    is_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    display_order INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_dimension_configurations_tenant_id (tenant_id),
    UNIQUE KEY uk_tenant_dimension_type (tenant_id, dimension_type)
);
```

## Model Definitions

### Expense Domain Models

#### ExpenseAllocation.php
```php
<?php

namespace App\Domain\Expense\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Expense\Enums\ExpenseAllocationStatus;
use App\Domain\Financial\Casts\BigDecimalCast;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                             $id
 * @property string                             $tenant_id
 * @property string                             $expense_id
 * @property BigDecimal                         $amount
 * @property ?string                            $note
 * @property ExpenseAllocationStatus            $status
 * @property Carbon                             $created_at
 * @property Carbon                             $updated_at
 * @property Expense                            $expense
 * @property Collection<int, AllocationDimension> $dimensions
 */
class ExpenseAllocation extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'expense_id',
        'amount',
        'note',
        'status',
    ];

    protected $casts = [
        'amount' => BigDecimalCast::class,
        'status' => ExpenseAllocationStatus::class,
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function dimensions(): HasMany
    {
        return $this->hasMany(AllocationDimension::class, 'allocation_id');
    }
}
```

#### AllocationDimension.php
```php
<?php

namespace App\Domain\Expense\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Expense\Enums\AllocationDimensionType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string                  $id
 * @property string                  $allocation_id
 * @property AllocationDimensionType $dimension_type
 * @property string                  $dimension_id
 * @property Carbon                  $created_at
 * @property Carbon                  $updated_at
 * @property ExpenseAllocation       $allocation
 * @property mixed                   $dimensionable
 */
class AllocationDimension extends BaseModel
{
    protected $fillable = [
        'allocation_id',
        'dimension_type',
        'dimension_id',
    ];

    protected $casts = [
        'dimension_type' => AllocationDimensionType::class,
    ];

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(ExpenseAllocation::class, 'allocation_id');
    }

    public function dimensionable(): MorphTo
    {
        return $this->morphTo('dimensionable', 'dimension_type', 'dimension_id');
    }
}
```

### Approval Domain Models

#### ApprovalWorkflow.php
```php
<?php

namespace App\Domain\Approval\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Financial\Casts\BigDecimalCast;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                           $id
 * @property string                           $tenant_id
 * @property string                           $name
 * @property ?string                          $description
 * @property ?BigDecimal                      $match_amount_min
 * @property ?BigDecimal                      $match_amount_max
 * @property ?array                           $match_conditions
 * @property int                              $priority
 * @property bool                             $is_active
 * @property string                           $created_by
 * @property Carbon                           $created_at
 * @property Carbon                           $updated_at
 * @property User                             $creator
 * @property Collection<int, WorkflowStep>    $steps
 * @property Collection<int, ExpenseApprovalExecution> $executions
 */
class ApprovalWorkflow extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'match_amount_min',
        'match_amount_max',
        'match_conditions',
        'priority',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'match_amount_min' => BigDecimalCast::class,
        'match_amount_max' => BigDecimalCast::class,
        'match_conditions' => 'array',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class, 'workflow_id')->orderBy('step_order');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(ExpenseApprovalExecution::class, 'workflow_id');
    }
}
```

### Global Dimension Models

#### TransactionType.php
```php
<?php

namespace App\Domain\Financial\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;

/**
 * @property string  $id
 * @property ?string $tenant_id
 * @property string  $code
 * @property string  $name
 * @property ?string $description
 * @property bool    $is_active
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class TransactionType extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name', 
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
```

#### CostType.php
```php
<?php

namespace App\Domain\Financial\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;

/**
 * @property string  $id
 * @property ?string $tenant_id
 * @property string  $code
 * @property string  $name
 * @property ?string $description
 * @property bool    $is_active
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class CostType extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description', 
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
```

#### RelatedTransactionCategory.php
```php
<?php

namespace App\Domain\Financial\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;

/**
 * @property string  $id
 * @property ?string $tenant_id
 * @property string  $code
 * @property string  $name
 * @property ?string $description
 * @property bool    $is_active
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class RelatedTransactionCategory extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
```

#### Location.php
```php
<?php

namespace App\Domain\Common\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;

/**
 * @property string  $id
 * @property ?string $tenant_id
 * @property string  $code
 * @property string  $name
 * @property ?string $description
 * @property ?string $address
 * @property bool    $is_active
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class Location extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
```

#### EquipmentType.php
```php
<?php

namespace App\Domain\Common\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;

/**
 * @property string  $id
 * @property ?string $tenant_id
 * @property string  $code
 * @property string  $name
 * @property ?string $description
 * @property bool    $is_active
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class EquipmentType extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
```

#### OrganizationUnitMembership.php
```php
<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Enums\UnitRoleLevel;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string            $id
 * @property string            $tenant_id
 * @property string            $user_id
 * @property string            $organization_unit_id
 * @property UnitRoleLevel     $role_level
 * @property bool              $is_primary
 * @property Carbon            $valid_from
 * @property ?Carbon           $valid_until
 * @property Carbon            $created_at
 * @property Carbon            $updated_at
 * @property User              $user
 * @property OrganizationUnit  $organizationUnit
 */
class OrganizationUnitMembership extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'organization_unit_id',
        'role_level',
        'is_primary',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'role_level' => UnitRoleLevel::class,
        'is_primary' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function isActive(): bool
    {
        $now = now();
        return $this->valid_from <= $now && 
               ($this->valid_until === null || $this->valid_until >= $now);
    }
}
```

#### OrganizationUnit.php
```php
<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string  $id
 * @property ?string $tenant_id
 * @property string  $code
 * @property string  $name
 * @property ?string $description
 * @property ?string $parent_id
 * @property bool    $is_active
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property ?OrganizationUnit $parent
 * @property Collection<int, OrganizationUnit> $children
 * @property Collection<int, OrganizationUnitMembership> $memberships
 * @property Collection<int, User> $members
 */
class OrganizationUnit extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'parent_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(OrganizationUnit::class, 'parent_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(OrganizationUnitMembership::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_unit_memberships')
            ->withPivot(['role_level', 'is_primary', 'valid_from', 'valid_until'])
            ->withTimestamps();
    }

    public function getOwnersAttribute(): Collection
    {
        return $this->memberships()
            ->where('role_level', UnitRoleLevel::UNIT_OWNER)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')
                      ->orWhere('valid_until', '>=', now());
            })
            ->with('user')
            ->get()
            ->pluck('user');
    }
}
```

#### RevenueType.php
```php
<?php

namespace App\Domain\Financial\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;

/**
 * @property string  $id
 * @property ?string $tenant_id
 * @property string  $code
 * @property string  $name
 * @property ?string $description
 * @property bool    $is_active
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class RevenueType extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
```

#### ProductCategory.php
```php
<?php

namespace App\Domain\Products\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;

/**
 * @property string  $id
 * @property ?string $tenant_id
 * @property string  $code
 * @property string  $name
 * @property ?string $description
 * @property bool    $is_active
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class ProductCategory extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
```

#### ContractType.php
```php
<?php

namespace App\Domain\Common\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;

/**
 * @property string  $id
 * @property ?string $tenant_id
 * @property string  $code
 * @property string  $name
 * @property ?string $description
 * @property bool    $is_active
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class ContractType extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
```

### Tenant Configuration Models

#### TenantDimensionConfiguration.php
```php
<?php

namespace App\Domain\Expense\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Expense\Enums\AllocationDimensionType;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string                  $id
 * @property string                  $tenant_id
 * @property AllocationDimensionType $dimension_type
 * @property bool                    $is_enabled
 * @property int                     $display_order
 * @property Carbon                  $created_at
 * @property Carbon                  $updated_at
 */
class TenantDimensionConfiguration extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'dimension_type',
        'is_enabled',
        'display_order',
    ];

    protected $casts = [
        'dimension_type' => AllocationDimensionType::class,
        'is_enabled' => 'boolean',
        'display_order' => 'integer',
    ];
}
```

## Enums

### ExpenseAllocationStatus.php
```php
<?php

namespace App\Domain\Expense\Enums;

enum ExpenseAllocationStatus: string
{
    case PENDING = 'pending';
    case ALLOCATED = 'allocated';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
```

### AllocationDimensionType.php
```php
<?php

namespace App\Domain\Expense\Enums;

enum AllocationDimensionType: string
{
    case EMPLOYEES = 'HA';          // Pracownicy
    case LOCATION = 'LO';           // Lokalizacja
    case PRODUCTS = 'PD';           // Produkty
    case PROJECT = 'PR';            // Projekt
    case REVENUE_TYPE = 'RS';       // Rodzaj przychodów
    case TRANSACTION_TYPE = 'RTR';  // Rodzaj transakcji
    case COST_TYPE = 'RY';          // Rodzaj kosztów
    case STRUCTURE = 'ST';          // Struktura/Działy
    case RELATED_TRANSACTIONS = 'TP'; // Transakcje powiązane
    case CONTRACTS = 'UM';          // Umowy
    case EQUIPMENT = 'UR';          // Urządzenia

    public function label(): string
    {
        return match ($this) {
            self::EMPLOYEES => 'Pracownicy',
            self::LOCATION => 'Lokalizacja',
            self::PRODUCTS => 'Produkty', 
            self::PROJECT => 'Projekt',
            self::REVENUE_TYPE => 'Rodzaj przychodów',
            self::TRANSACTION_TYPE => 'Rodzaj transakcji',
            self::COST_TYPE => 'Rodzaj kosztów',
            self::STRUCTURE => 'Struktura/Działy',
            self::RELATED_TRANSACTIONS => 'Transakcje powiązane',
            self::CONTRACTS => 'Umowy',
            self::EQUIPMENT => 'Urządzenia',
        };
    }

    public function getMorphClass(): string
    {
        return match ($this) {
            self::EMPLOYEES => \App\Domain\Auth\Models\User::class,
            self::LOCATION => \App\Domain\Common\Models\Location::class,
            self::PRODUCTS => \App\Domain\Products\Models\ProductCategory::class,
            self::PROJECT => \App\Domain\Projects\Models\Project::class,
            self::REVENUE_TYPE => \App\Domain\Financial\Models\RevenueType::class,
            self::TRANSACTION_TYPE => \App\Domain\Financial\Models\TransactionType::class,
            self::COST_TYPE => \App\Domain\Financial\Models\CostType::class,
            self::STRUCTURE => \App\Domain\Tenant\Models\OrganizationUnit::class,
            self::RELATED_TRANSACTIONS => \App\Domain\Financial\Models\RelatedTransactionCategory::class,
            self::CONTRACTS => \App\Domain\Common\Models\ContractType::class,
            self::EQUIPMENT => \App\Domain\Common\Models\EquipmentType::class,
        };
    }

    public function isAlwaysVisible(): bool
    {
        return $this === self::TRANSACTION_TYPE;
    }

    public function isConfigurable(): bool
    {
        return !$this->isAlwaysVisible();
    }

    public function getDefaultDisplayOrder(): int
    {
        return match ($this) {
            self::TRANSACTION_TYPE => 1,  // Always first
            self::PROJECT => 2,
            self::EMPLOYEES => 3,
            self::COST_TYPE => 4,
            self::STRUCTURE => 5,
            self::LOCATION => 6,
            self::PRODUCTS => 7,
            self::REVENUE_TYPE => 8,
            self::RELATED_TRANSACTIONS => 9,
            self::CONTRACTS => 10,
            self::EQUIPMENT => 11,
        };
    }
}
```

### ApprovalExecutionStatus.php
```php
<?php

namespace App\Domain\Approval\Enums;

enum ApprovalExecutionStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
}
```

### ApprovalDecision.php
```php
<?php

namespace App\Domain\Approval\Enums;

enum ApprovalDecision: string
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
```

### UnitRoleLevel.php
```php
<?php

namespace App\Domain\Tenant\Enums;

enum UnitRoleLevel: string
{
    case UNIT_MEMBER = 'unit-member';
    case UNIT_DEPUTY = 'unit-deputy';
    case UNIT_OWNER = 'unit-owner';
    case UNIT_ADMIN = 'unit-admin';

    public function label(): string
    {
        return match ($this) {
            self::UNIT_MEMBER => 'Member',
            self::UNIT_DEPUTY => 'Deputy Manager',
            self::UNIT_OWNER => 'Manager/Owner',
            self::UNIT_ADMIN => 'Administrator',
        };
    }

    public function labelPL(): string
    {
        return match ($this) {
            self::UNIT_MEMBER => 'Członek',
            self::UNIT_DEPUTY => 'Zastępca',
            self::UNIT_OWNER => 'Kierownik',
            self::UNIT_ADMIN => 'Administrator',
        };
    }

    public function getHierarchyLevel(): int
    {
        return match ($this) {
            self::UNIT_MEMBER => 1,
            self::UNIT_DEPUTY => 2,
            self::UNIT_OWNER => 3,
            self::UNIT_ADMIN => 4,
        };
    }

    public function canApproveFor(UnitRoleLevel $requestorLevel): bool
    {
        return $this->getHierarchyLevel() > $requestorLevel->getHierarchyLevel();
    }
}
```

### ApproverType.php
```php
<?php

namespace App\Domain\Approval\Enums;

enum ApproverType: string
{
    case USER = 'user';                    // Specific user ID
    case UNIT_ROLE = 'unit_role';          // Unit role level (unit-owner, unit-deputy, etc.)
    case SYSTEM_PERMISSION = 'system_permission'; // Spatie permission name

    public function label(): string
    {
        return match ($this) {
            self::USER => 'Specific User',
            self::UNIT_ROLE => 'Organization Unit Role',
            self::SYSTEM_PERMISSION => 'System Permission',
        };
    }
}
```

## Extended Expense Model

Update the existing `Expense` model to include allocation relationships:

```php
// Add to existing Expense model relationships:

public function allocations(): HasMany
{
    return $this->hasMany(ExpenseAllocation::class);
}

public function approvalExecution(): HasOne
{
    return $this->hasOne(ExpenseApprovalExecution::class);
}

// Add computed properties:
public function getTotalAllocatedAttribute(): BigDecimal
{
    return $this->allocations->sum('amount');
}

public function getIsFullyAllocatedAttribute(): bool
{
    return $this->total_gross->isEqualTo($this->getTotalAllocatedAttribute());
}

public function getRequiresApprovalAttribute(): bool
{
    return $this->approvalExecution && 
           $this->approvalExecution->status === ApprovalExecutionStatus::PENDING;
}
```

## Status Flow Integration

Extend the existing `InvoiceStatus` enum:

```php
// Add to existing InvoiceStatus enum:
case PENDING_ALLOCATION = 'pendingAllocation';
case ALLOCATED = 'allocated';
case PENDING_APPROVAL = 'pendingApproval';
case APPROVAL_REJECTED = 'approvalRejected';
case APPROVED = 'approved';
```

## Organizational Unit-Based Approval Logic

### How It Works

Instead of complex role/permission combinations, approvals now work with **organizational hierarchy**:

#### **Example Approval Rules**:
```php
// Simple rules like:
"Expenses under 1000 PLN require unit-owner approval from expense creator's unit"
"Expenses over 1000 PLN require unit-owner approval from both creator's unit AND parent unit"
"Expenses over 5000 PLN require additional approval from anyone with system permission 'approve_large_expenses'"
```

#### **Approval Flow Examples**:
1. **Employee** submits 500 PLN expense from "Sales" unit
   → Routes to **unit-owner** of "Sales" unit

2. **Deputy Manager** submits 1500 PLN expense from "Sales" unit  
   → Routes to **unit-owner** of "Sales" unit AND **unit-owner** of parent unit "Management"

3. **Manager** submits 200 PLN expense from "Sales" unit
   → Auto-approved (manager can approve their own small expenses)

#### **StepApprover Configuration Examples**:
```php
// Instead of assigning specific users, assign organizational roles:

// Step 1: Unit manager approval
StepApprover::create([
    'step_id' => $step->id,
    'approver_type' => ApproverType::UNIT_ROLE,
    'approver_value' => 'unit-owner',
    'organization_unit_id' => null, // Auto-detect from expense creator's unit
]);

// Step 2: Parent unit manager approval  
StepApprover::create([
    'step_id' => $step->id,
    'approver_type' => ApproverType::UNIT_ROLE,
    'approver_value' => 'unit-owner',
    'organization_unit_id' => 'PARENT_UNIT', // Special keyword
]);

// Step 3: System permission-based approval
StepApprover::create([
    'step_id' => $step->id,
    'approver_type' => ApproverType::SYSTEM_PERMISSION,
    'approver_value' => 'approve_large_expenses',
    'organization_unit_id' => null,
]);
```

## Services

### OrganizationUnitService.php
```php
<?php

namespace App\Domain\Tenant\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\OrganizationUnitMembership;
use App\Domain\Tenant\Enums\UnitRoleLevel;
use Illuminate\Database\Eloquent\Collection;

class OrganizationUnitService
{
    public function getUserPrimaryUnit(User $user): ?OrganizationUnit
    {
        $membership = OrganizationUnitMembership::where('user_id', $user->id)
            ->where('is_primary', true)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')
                      ->orWhere('valid_until', '>=', now());
            })
            ->first();

        return $membership?->organizationUnit;
    }

    public function getUsersWithRoleInUnit(OrganizationUnit $unit, UnitRoleLevel $roleLevel): Collection
    {
        return User::whereHas('organizationUnitMemberships', function ($query) use ($unit, $roleLevel) {
            $query->where('organization_unit_id', $unit->id)
                  ->where('role_level', $roleLevel)
                  ->where('valid_from', '<=', now())
                  ->where(function ($q) {
                      $q->whereNull('valid_until')
                        ->orWhere('valid_until', '>=', now());
                  });
        })->get();
    }

    public function canUserApproveForUnit(User $approver, OrganizationUnit $unit, UnitRoleLevel $minimumLevel): bool
    {
        $membership = OrganizationUnitMembership::where('user_id', $approver->id)
            ->where('organization_unit_id', $unit->id)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')
                      ->orWhere('valid_until', '>=', now());
            })
            ->first();

        if (!$membership) {
            return false;
        }

        return $membership->role_level->canApproveFor($minimumLevel);
    }
}
```

### DimensionVisibilityService.php
```php
<?php

namespace App\Domain\Expense\Services;

use App\Domain\Expense\Enums\AllocationDimensionType;
use App\Domain\Expense\Models\TenantDimensionConfiguration;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class DimensionVisibilityService
{
    public function getEnabledDimensionsForTenant(?string $tenantId = null): Collection
    {
        $tenantId = $tenantId ?? Auth::user()?->getTenantId();
        
        if (!$tenantId) {
            return collect();
        }

        // Get tenant configurations
        $configurations = TenantDimensionConfiguration::where('tenant_id', $tenantId)
            ->where('is_enabled', true)
            ->orderBy('display_order')
            ->get()
            ->keyBy('dimension_type');

        // Always include RTR (Transaction Type)
        $enabledDimensions = collect([AllocationDimensionType::TRANSACTION_TYPE]);

        // Add configured dimensions
        foreach (AllocationDimensionType::cases() as $dimension) {
            if ($dimension->isConfigurable() && $configurations->has($dimension->value)) {
                $enabledDimensions->push($dimension);
            }
        }

        return $enabledDimensions->sortBy(function ($dimension) use ($configurations) {
            if ($dimension === AllocationDimensionType::TRANSACTION_TYPE) {
                return 0; // Always first
            }
            return $configurations->get($dimension->value)?->display_order ?? 999;
        })->values();
    }

    public function initializeDefaultConfigurationForTenant(string $tenantId): void
    {
        foreach (AllocationDimensionType::cases() as $dimension) {
            if ($dimension->isConfigurable()) {
                TenantDimensionConfiguration::create([
                    'tenant_id' => $tenantId,
                    'dimension_type' => $dimension,
                    'is_enabled' => $this->getDefaultEnabledState($dimension),
                    'display_order' => $dimension->getDefaultDisplayOrder(),
                ]);
            }
        }
    }

    private function getDefaultEnabledState(AllocationDimensionType $dimension): bool
    {
        // Enable common dimensions by default
        return match ($dimension) {
            AllocationDimensionType::PROJECT,
            AllocationDimensionType::EMPLOYEES,
            AllocationDimensionType::COST_TYPE,
            AllocationDimensionType::STRUCTURE => true,
            default => false,
        };
    }
}
```

### ApprovalResolutionService.php
```php
<?php

namespace App\Domain\Approval\Services;

use App\Domain\Approval\Enums\ApproverType;
use App\Domain\Approval\Models\StepApprover;
use App\Domain\Auth\Models\User;
use App\Domain\Expense\Models\Expense;
use App\Domain\Tenant\Services\OrganizationUnitService;
use App\Domain\Tenant\Enums\UnitRoleLevel;
use Illuminate\Database\Eloquent\Collection;

class ApprovalResolutionService
{
    public function __construct(
        private OrganizationUnitService $unitService
    ) {}

    public function resolveApproversForStep(StepApprover $stepApprover, Expense $expense): Collection
    {
        return match ($stepApprover->approver_type) {
            ApproverType::USER => $this->resolveSpecificUser($stepApprover),
            ApproverType::UNIT_ROLE => $this->resolveUnitRole($stepApprover, $expense),
            ApproverType::SYSTEM_PERMISSION => $this->resolveSystemPermission($stepApprover),
        };
    }

    private function resolveSpecificUser(StepApprover $stepApprover): Collection
    {
        $user = User::find($stepApprover->approver_value);
        return $user ? collect([$user]) : collect();
    }

    private function resolveUnitRole(StepApprover $stepApprover, Expense $expense): Collection
    {
        $roleLevel = UnitRoleLevel::from($stepApprover->approver_value);
        
        // Determine which organizational unit to look in
        if ($stepApprover->organization_unit_id) {
            if ($stepApprover->organization_unit_id === 'PARENT_UNIT') {
                $creatorUnit = $this->unitService->getUserPrimaryUnit($expense->createdBy);
                $targetUnit = $creatorUnit?->parent;
            } else {
                $targetUnit = OrganizationUnit::find($stepApprover->organization_unit_id);
            }
        } else {
            // Default: use expense creator's primary unit
            $targetUnit = $this->unitService->getUserPrimaryUnit($expense->createdBy);
        }

        if (!$targetUnit) {
            return collect();
        }

        return $this->unitService->getUsersWithRoleInUnit($targetUnit, $roleLevel);
    }

    private function resolveSystemPermission(StepApprover $stepApprover): Collection
    {
        return User::permission($stepApprover->approver_value)->get();
    }
}
```

### WorkflowMatchingService.php
```php
<?php

namespace App\Domain\Approval\Services;

use App\Domain\Approval\Models\ApprovalWorkflow;
use App\Domain\Expense\Models\Expense;
use Illuminate\Database\Eloquent\Collection;

class WorkflowMatchingService
{
    public function findMatchingWorkflow(Expense $expense): ?ApprovalWorkflow
    {
        $workflows = ApprovalWorkflow::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        foreach ($workflows as $workflow) {
            if ($this->matchesWorkflow($expense, $workflow)) {
                return $workflow;
            }
        }

        return null;
    }

    private function matchesWorkflow(Expense $expense, ApprovalWorkflow $workflow): bool
    {
        // Amount range check
        if ($workflow->match_amount_min && $expense->total_gross->isLessThan($workflow->match_amount_min)) {
            return false;
        }

        if ($workflow->match_amount_max && $expense->total_gross->isGreaterThan($workflow->match_amount_max)) {
            return false;
        }

        // Conditional matching based on allocations
        if ($workflow->match_conditions) {
            return $this->evaluateConditions($expense, $workflow->match_conditions);
        }

        return true;
    }

    private function evaluateConditions(Expense $expense, array $conditions): bool
    {
        // Implement complex conditional logic
        // Example: check if expense has specific project allocations, etc.
        
        foreach ($conditions as $condition) {
            if (!$this->evaluateCondition($expense, $condition)) {
                return false;
            }
        }

        return true;
    }
}
```

## Actions

### AllocateExpenseAction.php
```php
<?php

namespace App\Domain\Expense\Actions;

use App\Domain\Expense\Models\Expense;
use App\Domain\Expense\Models\ExpenseAllocation;
use App\Domain\Expense\Models\AllocationDimension;
use App\Domain\Financial\Enums\InvoiceStatus;
use Illuminate\Support\Facades\DB;

class AllocateExpenseAction
{
    public function execute(Expense $expense, array $allocations): void
    {
        DB::transaction(function () use ($expense, $allocations) {
            // Clear existing allocations
            $expense->allocations()->delete();

            foreach ($allocations as $allocationData) {
                $allocation = ExpenseAllocation::create([
                    'tenant_id' => $expense->tenant_id,
                    'expense_id' => $expense->id,
                    'amount' => $allocationData['amount'],
                    'note' => $allocationData['note'] ?? null,
                ]);

                // Create dimension associations
                foreach ($allocationData['dimensions'] as $dimension) {
                    AllocationDimension::create([
                        'allocation_id' => $allocation->id,
                        'dimension_type' => $dimension['type'],
                        'dimension_id' => $dimension['id'],
                    ]);
                }
            }

            // Update expense status
            $expense->update(['status' => InvoiceStatus::ALLOCATED]);

            // Trigger approval workflow
            app(StartApprovalWorkflowAction::class)->execute($expense);
        });
    }
}
```

### StartApprovalWorkflowAction.php
```php
<?php

namespace App\Domain\Approval\Actions;

use App\Domain\Approval\Models\ExpenseApprovalExecution;
use App\Domain\Approval\Services\WorkflowMatchingService;
use App\Domain\Expense\Models\Expense;
use App\Domain\Financial\Enums\InvoiceStatus;

class StartApprovalWorkflowAction
{
    public function __construct(
        private WorkflowMatchingService $workflowMatcher
    ) {}

    public function execute(Expense $expense): ?ExpenseApprovalExecution
    {
        $workflow = $this->workflowMatcher->findMatchingWorkflow($expense);
        
        if (!$workflow) {
            // No workflow matches - auto-approve
            $expense->update(['status' => InvoiceStatus::APPROVED]);
            return null;
        }

        $execution = ExpenseApprovalExecution::create([
            'expense_id' => $expense->id,
            'workflow_id' => $workflow->id,
            'current_step_id' => $workflow->steps->first()->id,
            'started_at' => now(),
        ]);

        $expense->update(['status' => InvoiceStatus::PENDING_APPROVAL]);

        // Send notifications to first step approvers
        app(ApprovalNotificationService::class)->notifyStepApprovers($execution);

        return $execution;
    }
}
```

## Integration Points

### OCR Integration
Modify `ApplyOcrResultToExpenseAction` to automatically start allocation after OCR completion:

```php
// Add to existing ApplyOcrResultToExpenseAction::handle()
$expense->status = InvoiceStatus::PENDING_ALLOCATION;
$expense->save();

// Auto-create basic allocation if possible
if ($this->canAutoAllocate($expense)) {
    app(AllocateExpenseAction::class)->execute($expense, $this->generateAutoAllocation($expense));
}
```

### Workflow Configuration Examples

#### Example 1: Simple Unit Manager Approval
```php
// Workflow: "Small Expenses" (under 1000 PLN)
$workflow = ApprovalWorkflow::create([
    'tenant_id' => $tenant->id,
    'name' => 'Small Expenses',
    'match_amount_max' => 1000,
    'priority' => 1,
]);

$step = WorkflowStep::create([
    'workflow_id' => $workflow->id,
    'step_order' => 1,
    'name' => 'Unit Manager Approval',
    'min_approvers' => 1,
]);

StepApprover::create([
    'step_id' => $step->id,
    'approver_type' => ApproverType::UNIT_ROLE,
    'approver_value' => UnitRoleLevel::UNIT_OWNER->value,
    'organization_unit_id' => null, // Creator's unit
]);
```

#### Example 2: Hierarchical Approval
```php
// Workflow: "Large Expenses" (over 1000 PLN)
$workflow = ApprovalWorkflow::create([
    'tenant_id' => $tenant->id,
    'name' => 'Large Expenses',
    'match_amount_min' => 1000.01,
    'priority' => 2,
]);

// Step 1: Unit Manager
$step1 = WorkflowStep::create([
    'workflow_id' => $workflow->id,
    'step_order' => 1,
    'name' => 'Unit Manager Approval',
    'min_approvers' => 1,
]);

StepApprover::create([
    'step_id' => $step1->id,
    'approver_type' => ApproverType::UNIT_ROLE,
    'approver_value' => UnitRoleLevel::UNIT_OWNER->value,
    'organization_unit_id' => null, // Creator's unit
]);

// Step 2: Parent Unit Manager
$step2 = WorkflowStep::create([
    'workflow_id' => $workflow->id,
    'step_order' => 2,
    'name' => 'Parent Unit Manager Approval',
    'min_approvers' => 1,
]);

StepApprover::create([
    'step_id' => $step2->id,
    'approver_type' => ApproverType::UNIT_ROLE,
    'approver_value' => UnitRoleLevel::UNIT_OWNER->value,
    'organization_unit_id' => 'PARENT_UNIT',
]);
```

#### Example 3: System Permission-Based Approval
```php
// Workflow: "Very Large Expenses" (over 5000 PLN)
$workflow = ApprovalWorkflow::create([
    'tenant_id' => $tenant->id,
    'name' => 'Very Large Expenses',
    'match_amount_min' => 5000.01,
    'priority' => 3,
]);

$step = WorkflowStep::create([
    'workflow_id' => $workflow->id,
    'step_order' => 1,
    'name' => 'CFO/Finance Director Approval',
    'min_approvers' => 1,
]);

StepApprover::create([
    'step_id' => $step->id,
    'approver_type' => ApproverType::SYSTEM_PERMISSION,
    'approver_value' => 'approve_large_expenses',
    'organization_unit_id' => null,
]);
```

### API Endpoints

```php
// routes/api/expenses.php additions:
Route::post('expenses/{expense}/allocate', [ExpenseAllocationController::class, 'store']);
Route::get('expenses/{expense}/allocations', [ExpenseAllocationController::class, 'index']);
Route::post('expenses/{expense}/approve', [ExpenseApprovalController::class, 'approve']);
Route::post('expenses/{expense}/reject', [ExpenseApprovalController::class, 'reject']);

// routes/api/approval.php (new):
Route::apiResource('approval-workflows', ApprovalWorkflowController::class);
Route::get('pending-approvals', [ApprovalController::class, 'pendingApprovals']);
Route::post('delegations', [ApprovalDelegationController::class, 'store']);

// routes/api/dimensions.php (new):
Route::get('dimension-configurations', [DimensionConfigurationController::class, 'index']);
Route::put('dimension-configurations', [DimensionConfigurationController::class, 'update']);
Route::get('available-dimensions', [DimensionConfigurationController::class, 'availableDimensions']);

// routes/api/organization-units.php (new):
Route::apiResource('organization-units', OrganizationUnitController::class);
Route::post('organization-units/{unit}/memberships', [OrganizationUnitMembershipController::class, 'store']);
Route::put('organization-units/{unit}/memberships/{membership}', [OrganizationUnitMembershipController::class, 'update']);
Route::delete('organization-units/{unit}/memberships/{membership}', [OrganizationUnitMembershipController::class, 'destroy']);
Route::get('organization-units/{unit}/members', [OrganizationUnitController::class, 'members']);
```

## Global Dimension Seeders

### DefaultTransactionTypesSeeder.php
```php
<?php

namespace Database\Seeders;

use App\Domain\Financial\Models\TransactionType;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;

class DefaultTransactionTypesSeeder extends Seeder
{
    public function run(): void
    {
        $defaultTypes = [
            [
                'code' => '10_zakup_materialow_produkcyjnych',
                'name' => 'Zakup materiałów produkcyjnych',
                'description' => 'Zakup materiałów używanych w procesie produkcyjnym',
            ],
            [
                'code' => '10_zakup_towarow',
                'name' => 'Zakup towarów',
                'description' => 'Standardowy zakup towarów handlowych',
            ],
            [
                'code' => '10_zakup_towarow_odwrotne_obciazenie',
                'name' => 'Zakup towarów - odwrotne obciążenie',
                'description' => 'Zakup towarów z zastosowaniem mechanizmu odwrotnego obciążenia VAT',
            ],
            [
                'code' => '20_sprzedaz_towarow',
                'name' => 'Sprzedaż towarów',
                'description' => 'Standardowa sprzedaż towarów handlowych',
            ],
            [
                'code' => '30_uslugi_zewnetrzne',
                'name' => 'Usługi zewnętrzne',
                'description' => 'Zakup usług od zewnętrznych dostawców',
            ],
        ];

        foreach ($defaultTypes as $type) {
            TransactionType::create([
                'tenant_id' => null, // Global
                ...$type,
                'is_active' => true,
            ]);
        }
    }
}
```

### DefaultCostTypesSeeder.php
```php
<?php

namespace Database\Seeders;

use App\Domain\Financial\Models\CostType;
use Illuminate\Database\Seeder;

class DefaultCostTypesSeeder extends Seeder
{
    public function run(): void
    {
        $defaultTypes = [
            [
                'code' => '01_koszty_materialow',
                'name' => 'Koszty materiałów',
                'description' => 'Koszty zakupu materiałów produkcyjnych i biurowych',
            ],
            [
                'code' => '02_koszty_pracy',
                'name' => 'Koszty pracy',
                'description' => 'Wynagrodzenia, składki ZUS, świadczenia pracownicze',
            ],
            [
                'code' => '03_koszty_ogolne',
                'name' => 'Koszty ogólne',
                'description' => 'Koszty administracyjne, biurowe, utrzymania',
            ],
            [
                'code' => '04_koszty_sprzedazy',
                'name' => 'Koszty sprzedaży',
                'description' => 'Koszty marketingu, reklamy, dystrybucji',
            ],
            [
                'code' => '05_koszty_finansowe',
                'name' => 'Koszty finansowe',
                'description' => 'Odsetki, prowizje bankowe, różnice kursowe',
            ],
        ];

        foreach ($defaultTypes as $type) {
            CostType::create([
                'tenant_id' => null, // Global
                ...$type,
                'is_active' => true,
            ]);
        }
    }
}
```

### DefaultRelatedTransactionCategoriesSeeder.php
```php
<?php

namespace Database\Seeders;

use App\Domain\Financial\Models\RelatedTransactionCategory;
use Illuminate\Database\Seeder;

class DefaultRelatedTransactionCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $defaultCategories = [
            [
                'code' => 'import_transaction',
                'name' => 'Transakcja importowa',
                'description' => 'Zakup towarów lub usług z zagranicy',
            ],
            [
                'code' => 'export_transaction', 
                'name' => 'Transakcja eksportowa',
                'description' => 'Sprzedaż towarów lub usług za granicę',
            ],
            [
                'code' => 'internal_transfer',
                'name' => 'Transfer wewnętrzny',
                'description' => 'Przekazanie między jednostkami organizacyjnymi',
            ],
            [
                'code' => 'correction_transaction',
                'name' => 'Transakcja korygująca',
                'description' => 'Korekta wcześniej zaksięgowanych operacji',
            ],
        ];

        foreach ($defaultCategories as $category) {
            RelatedTransactionCategory::create([
                'tenant_id' => null, // Global
                ...$category,
                'is_active' => true,
            ]);
        }
    }
}
```

### DefaultLocationsSeeder.php
```php
<?php

namespace Database\Seeders;

use App\Domain\Common\Models\Location;
use Illuminate\Database\Seeder;

class DefaultLocationsSeeder extends Seeder
{
    public function run(): void
    {
        $defaultLocations = [
            [
                'code' => 'headquarters',
                'name' => 'Siedziba główna',
                'description' => 'Główne biuro firmy',
            ],
            [
                'code' => 'warehouse',
                'name' => 'Magazyn',
                'description' => 'Główny magazyn',
            ],
            [
                'code' => 'retail_store',
                'name' => 'Sklep detaliczny',
                'description' => 'Punkt sprzedaży detalicznej',
            ],
            [
                'code' => 'home_office',
                'name' => 'Biuro domowe',
                'description' => 'Praca zdalna z domu',
            ],
        ];

        foreach ($defaultLocations as $location) {
            Location::create([
                'tenant_id' => null, // Global
                ...$location,
                'is_active' => true,
            ]);
        }
    }
}
```

### DefaultEquipmentTypesSeeder.php
```php
<?php

namespace Database\Seeders;

use App\Domain\Common\Models\EquipmentType;
use Illuminate\Database\Seeder;

class DefaultEquipmentTypesSeeder extends Seeder
{
    public function run(): void
    {
        $defaultTypes = [
            [
                'code' => 'computer_hardware',
                'name' => 'Sprzęt komputerowy',
                'description' => 'Komputery, laptopy, monitory',
            ],
            [
                'code' => 'office_furniture',
                'name' => 'Meble biurowe',
                'description' => 'Biurka, krzesła, szafy',
            ],
            [
                'code' => 'vehicles',
                'name' => 'Pojazdy',
                'description' => 'Samochody służbowe, ciężarówki',
            ],
            [
                'code' => 'production_equipment',
                'name' => 'Sprzęt produkcyjny',
                'description' => 'Maszyny i urządzenia produkcyjne',
            ],
        ];

        foreach ($defaultTypes as $type) {
            EquipmentType::create([
                'tenant_id' => null, // Global
                ...$type,
                'is_active' => true,
            ]);
        }
    }
}
```

### DefaultOrganizationUnitsSeeder.php
```php
<?php

namespace Database\Seeders;

use App\Domain\Tenant\Models\OrganizationUnit;
use Illuminate\Database\Seeder;

class DefaultOrganizationUnitsSeeder extends Seeder
{
    public function run(): void
    {
        $defaultUnits = [
            [
                'code' => 'management',
                'name' => 'Zarząd',
                'description' => 'Kierownictwo firmy',
            ],
            [
                'code' => 'administration',
                'name' => 'Administracja',
                'description' => 'Działy administracyjne',
            ],
            [
                'code' => 'sales',
                'name' => 'Sprzedaż',
                'description' => 'Dział sprzedaży',
            ],
            [
                'code' => 'production',
                'name' => 'Produkcja',
                'description' => 'Dział produkcyjny',
            ],
            [
                'code' => 'it_department',
                'name' => 'Dział IT',
                'description' => 'Dział informatyczny',
            ],
        ];

        foreach ($defaultUnits as $unit) {
            OrganizationUnit::create([
                'tenant_id' => null, // Global
                ...$unit,
                'is_active' => true,
            ]);
        }
    }
}
```

### TenantDimensionConfigurationSeeder.php
```php
<?php

namespace Database\Seeders;

use App\Domain\Expense\Services\DimensionVisibilityService;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantDimensionConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $dimensionService = app(DimensionVisibilityService::class);
        
        // Initialize default configurations for all existing tenants
        Tenant::chunk(100, function ($tenants) use ($dimensionService) {
            foreach ($tenants as $tenant) {
                $dimensionService->initializeDefaultConfigurationForTenant($tenant->id);
            }
        });
    }
}
```

## Implementation Plan

### Phase 1: Database & Core Models
1. Create migrations for all tables (including global dimension tables, tenant configurations, and organizational unit memberships)
2. Implement base models and enums with dimension visibility logic and organizational unit roles
3. Create global dimension models with `IsGlobalOrBelongsToTenant` trait
4. Implement `TenantDimensionConfiguration` model and `DimensionVisibilityService`
5. Create organizational unit models: `OrganizationUnit`, `OrganizationUnitMembership` 
6. Implement unit role enums: `UnitRoleLevel`, `ApproverType`
7. Run global dimension seeders for default values
8. Create tenant dimension configuration seeder
9. Update existing Expense model relationships
10. Add new status values to InvoiceStatus

### Phase 2: Allocation System
1. Implement ExpenseAllocation and AllocationDimension models
2. Create AllocateExpenseAction with dimension visibility filtering
3. Build allocation API endpoints with tenant-specific dimension lists
4. Create dimension configuration management endpoints
5. Integrate with OCR workflow

### Phase 3: Approval Workflows
1. Implement ApprovalWorkflow and related models with organizational unit support
2. Create WorkflowMatchingService and ApprovalResolutionService
3. Implement OrganizationUnitService for unit-based role management
4. Build approval execution logic with unit hierarchy support
5. Add approval API endpoints and organizational unit management endpoints

### Phase 4: Advanced Features
1. Implement allocation templates
2. Add approval delegation system
3. Create notification system
4. Build approval analytics (future)

### Phase 5: Integration & Polish
1. Update existing controllers and resources
2. Add proper validation and policies
3. Create comprehensive tests
4. Update API documentation

## Testing Strategy

### Unit Tests
- Model relationships and business logic
- Enum functionality and status transitions
- Service class logic (workflow matching, allocation)

### Feature Tests
- Complete allocation workflow
- Approval process with various scenarios
- OCR integration with allocation
- API endpoint functionality

### Integration Tests
- Multi-tenant data isolation
- Complex workflow scenarios
- Performance with large datasets

This specification provides a complete, implementation-ready foundation that integrates seamlessly with your existing SaaSBase architecture while adding powerful allocation and approval capabilities.
