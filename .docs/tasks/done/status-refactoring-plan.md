# Invoice Status Architecture Refactoring

## Problem Statement

The current `InvoiceStatus` enum tries to handle multiple orthogonal concerns in a single status field, creating logical conflicts. For example, an invoice cannot be both "Sent" and "Overdue" simultaneously with the current design, even though these represent different aspects of the invoice lifecycle.

## Current Issues

1. **Single status limitation**: Cannot represent multiple simultaneous states
2. **Mixed concerns**: OCR, allocation, approval, delivery, and payment statuses are conflated
3. **Complex state machine**: The `canTransitionTo()` method is overly complex due to mixed concerns
4. **Limited querying**: Cannot efficiently query for invoices with specific combinations of states

## Proposed Solution

Split the single `InvoiceStatus` into separate, focused enums for each concern:

### New Status Enums

1. **InvoiceStatus** (General workflow state)
   - `DRAFT`, `PROCESSING`, `READY`, `ACTIVE`, `COMPLETED`, `CANCELLED`

2. **OcrRequestStatus** (Already exists)
   - `Pending`, `Processing`, `Completed`, `Failed`

3. **AllocationStatus** (New)
   - `NOT_REQUIRED`, `PENDING`, `PARTIALLY_ALLOCATED`, `FULLY_ALLOCATED`

4. **ApprovalStatus** (New)
   - `NOT_REQUIRED`, `PENDING`, `APPROVED`, `REJECTED`, `CANCELLED`

5. **DeliveryStatus** (New)
   - `NOT_SENT`, `PENDING`, `SENT`, `DELIVERED`, `FAILED`

6. **PaymentStatus** (Enhanced existing)
   - `PENDING`, `PARTIALLY_PAID`, `PAID`, `OVERDUE`, `CANCELLED`

## Benefits

### ✅ Solves Original Problem
- Invoice can be `DeliveryStatus::SENT` + `PaymentStatus::OVERDUE` simultaneously
- Each status dimension is independent and can change separately

### ✅ Better Separation of Concerns
- Each enum handles one specific aspect of the invoice lifecycle
- Cleaner, more focused state machines
- Easier to understand and maintain

### ✅ Improved Querying
```sql
-- Find all sent but unpaid invoices
SELECT * FROM invoices 
WHERE delivery_status = 'sent' 
AND payment_status IN ('pending', 'overdue', 'partiallyPaid')

-- Find invoices needing attention
SELECT * FROM invoices 
WHERE payment_status = 'overdue' 
OR ocr_status = 'failed' 
OR approval_status = 'rejected'
```

### ✅ Flexible Workflow Management
- Can easily add new statuses to specific dimensions
- Business rules can be applied per dimension
- Better support for parallel processing workflows

## Implementation Plan

### Phase 1: Create New Enums ✅
- [x] `ApprovalStatus` enum
- [x] `AllocationStatus` enum  
- [x] `DeliveryStatus` enum
- [x] Enhanced `PaymentStatus` enum
- [x] Simplified `InvoiceStatus` enum

### Phase 2: Create Supporting Infrastructure ✅
- [x] `InvoiceStatusDTO` for comprehensive status tracking
- [x] `InvoiceStatusService` for status management and transitions
- [x] Example demonstrating the new architecture

### Phase 3: Database Migration (Next Steps)
```sql
-- Add new status columns to invoices table
ALTER TABLE invoices ADD COLUMN ocr_status VARCHAR(20) DEFAULT 'pending';
ALTER TABLE invoices ADD COLUMN allocation_status VARCHAR(30) DEFAULT 'notRequired';
ALTER TABLE invoices ADD COLUMN approval_status VARCHAR(20) DEFAULT 'notRequired';
ALTER TABLE invoices ADD COLUMN delivery_status VARCHAR(20) DEFAULT 'notSent';
ALTER TABLE invoices ADD COLUMN payment_status VARCHAR(20) DEFAULT 'pending';

-- Add indexes for common queries
CREATE INDEX idx_invoices_delivery_status ON invoices(delivery_status);
CREATE INDEX idx_invoices_payment_status ON invoices(payment_status);
CREATE INDEX idx_invoices_approval_status ON invoices(approval_status);
```

### Phase 4: Model Updates
```php
// Update Invoice model
class Invoice extends BaseModel 
{
    protected $casts = [
        'status' => InvoiceStatus::class,
        'ocr_status' => OcrRequestStatus::class,
        'allocation_status' => AllocationStatus::class,
        'approval_status' => ApprovalStatus::class,
        'delivery_status' => DeliveryStatus::class,
        'payment_status' => PaymentStatus::class,
    ];

    public function getStatusDTO(): InvoiceStatusDTO
    {
        return new InvoiceStatusDTO(
            general: $this->status,
            ocr: $this->ocr_status,
            allocation: $this->allocation_status,
            approval: $this->approval_status,
            delivery: $this->delivery_status,
            payment: $this->payment_status,
        );
    }
}
```

### Phase 5: Update Business Logic
1. Update controllers to use new status fields
2. Modify existing actions and services
3. Update OCR completion logic
4. Update allocation and approval workflows
5. Update payment processing

### Phase 6: Frontend Updates
1. Update status displays to show multiple dimensions
2. Create status-specific filters and views
3. Update notification logic
4. Modify reporting and dashboards

### Phase 7: Data Migration
```php
// Migration script to populate new status fields from existing data
public function migrateExistingStatuses()
{
    Invoice::chunk(100, function ($invoices) {
        foreach ($invoices as $invoice) {
            $newStatuses = $this->mapOldStatusToNew($invoice->status);
            $invoice->update($newStatuses);
        }
    });
}

private function mapOldStatusToNew(InvoiceStatus $oldStatus): array
{
    return match ($oldStatus) {
        InvoiceStatus::DRAFT => [
            'status' => InvoiceStatus::DRAFT,
            'ocr_status' => OcrRequestStatus::Pending,
            'allocation_status' => AllocationStatus::NOT_REQUIRED,
            'approval_status' => ApprovalStatus::NOT_REQUIRED,
            'delivery_status' => DeliveryStatus::NOT_SENT,
            'payment_status' => PaymentStatus::PENDING,
        ],
        // ... other mappings
    };
}
```

### Phase 8: Remove Legacy Code
1. Remove old status handling logic
2. Clean up deprecated methods
3. Update documentation

## Real-World Examples

### Example 1: Sent + Overdue Invoice
```php
$invoice = new InvoiceStatusDTO(
    general: InvoiceStatus::ACTIVE,
    ocr: OcrRequestStatus::Completed,
    allocation: AllocationStatus::FULLY_ALLOCATED,
    approval: ApprovalStatus::APPROVED,
    delivery: DeliveryStatus::SENT,        // ✓ SENT
    payment: PaymentStatus::OVERDUE        // ✓ OVERDUE
);

echo $invoice->delivery->label();  // "Sent"
echo $invoice->payment->label();   // "Overdue"
// Both states exist simultaneously! 🎯
```

### Example 2: Complex Processing State
```php
$invoice = new InvoiceStatusDTO(
    general: InvoiceStatus::PROCESSING,
    ocr: OcrRequestStatus::Completed,      // ✓ OCR done
    allocation: AllocationStatus::PENDING, // ⏳ Needs allocation
    approval: ApprovalStatus::PENDING,     // ⏳ Needs approval  
    delivery: DeliveryStatus::NOT_SENT,    // ⏳ Not ready yet
    payment: PaymentStatus::PENDING
);

// Can easily query for invoices at this specific stage
```

### Example 3: Failed States Needing Attention
```php
$invoice = new InvoiceStatusDTO(
    general: InvoiceStatus::PROCESSING,
    ocr: OcrRequestStatus::Failed,         // ❌ OCR failed
    allocation: AllocationStatus::NOT_REQUIRED,
    approval: ApprovalStatus::REJECTED,    // ❌ Approval rejected
    delivery: DeliveryStatus::NOT_SENT,
    payment: PaymentStatus::PENDING
);

// Easy to identify what specifically needs attention
```

## Database Schema Changes

### New Columns
```sql
invoices:
  - status (simplified: draft, processing, ready, active, completed, cancelled)
  - ocr_status (pending, processing, completed, failed)
  - allocation_status (notRequired, pending, partiallyAllocated, fullyAllocated)
  - approval_status (notRequired, pending, approved, rejected, cancelled)
  - delivery_status (notSent, pending, sent, delivered, failed)
  - payment_status (pending, partiallyPaid, paid, overdue, cancelled)
```

### Query Examples
```sql
-- Invoices sent but payment overdue (the original problem!)
SELECT * FROM invoices 
WHERE delivery_status = 'sent' AND payment_status = 'overdue';

-- Invoices ready for allocation
SELECT * FROM invoices 
WHERE status = 'processing' 
AND ocr_status = 'completed' 
AND allocation_status = 'pending';

-- Invoices needing immediate attention
SELECT * FROM invoices 
WHERE payment_status = 'overdue' 
OR ocr_status = 'failed' 
OR approval_status = 'rejected' 
OR delivery_status = 'failed';
```

## Migration Timeline

1. **Week 1**: Create new enums and infrastructure ✅
2. **Week 2**: Database migration and model updates
3. **Week 3**: Update business logic and services
4. **Week 4**: Frontend updates and testing
5. **Week 5**: Data migration and cleanup
6. **Week 6**: Production deployment and monitoring

## Conclusion

This refactoring solves the core problem of status conflicts while providing:
- **Better separation of concerns**
- **More flexible querying capabilities**
- **Improved workflow management**
- **Cleaner, maintainable code**

The new architecture supports complex real-world scenarios where invoices exist in multiple states simultaneously, making the system more accurate and useful for business operations. 
