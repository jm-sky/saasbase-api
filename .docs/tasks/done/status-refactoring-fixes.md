# Status Refactoring - Immediate Fixes

## Overview

This document summarizes the immediate fixes applied to resolve Larastan errors after refactoring the `InvoiceStatus` enum architecture. These are **interim fixes** that allow the code to run while maintaining the new status architecture design.

## Fixes Applied

### 1. AllocateExpenseAction.php
- **Line 53**: `InvoiceStatus::ALLOCATED` → `InvoiceStatus::PROCESSING`
- **Line 162-163**: Removed `requiresAllocation()` method call and `PENDING_ALLOCATION` constant
  - New logic: `$expense->status === InvoiceStatus::PROCESSING || $expense->status === InvoiceStatus::DRAFT`

### 2. ApplyOcrResultToExpenseAction.php
- **Line 103**: `InvoiceStatus::OCR_COMPLETED` → `InvoiceStatus::PROCESSING`

### 3. CreateExpenseForOcr.php
- **Line 32**: `InvoiceStatus::OCR_PROCESSING` → `InvoiceStatus::PROCESSING`

### 4. ExpenseAllocationController.php
- **Line 173**: `InvoiceStatus::PENDING_ALLOCATION` → `InvoiceStatus::PROCESSING`

### 5. FinishOcrJob.php
- **Line 64**: `InvoiceStatus::OCR_FAILED` → `InvoiceStatus::DRAFT`

### 6. Factory Files (ExpenseFactory.php & InvoiceFactory.php)
- **sent() method**: `InvoiceStatus::SENT` → `InvoiceStatus::ACTIVE`
- **paid() method**: `InvoiceStatus::PAID` → `InvoiceStatus::COMPLETED`

## Current Status Mapping

| Old Status | New Status | Rationale |
|------------|------------|-----------|
| `ALLOCATED` | `PROCESSING` | Allocation is part of processing workflow |
| `OCR_COMPLETED` | `PROCESSING` | OCR completion moves to processing state |
| `OCR_PROCESSING` | `PROCESSING` | Still in processing workflow |
| `OCR_FAILED` | `DRAFT` | Reset to draft for reprocessing |
| `PENDING_ALLOCATION` | `PROCESSING` | Part of processing workflow |
| `SENT` | `ACTIVE` | Invoice has been sent and is active |
| `PAID` | `COMPLETED` | Invoice is fully completed |

## ✅ Larastan Status
All PHPStan/Larastan errors have been resolved. The code now compiles without errors.

## 🚨 Important Notes

### This is a Transitional State
These fixes maintain compatibility while the full status architecture is implemented. The ideal implementation would:

1. **Add separate status fields to models**:
   ```php
   // In Invoice/Expense models
   protected $casts = [
       'general_status' => InvoiceStatus::class,
       'ocr_status' => OcrRequestStatus::class,
       'allocation_status' => AllocationStatus::class,
       'approval_status' => ApprovalStatus::class,
       'delivery_status' => DeliveryStatus::class,
       'payment_status' => PaymentStatus::class,
   ];
   ```

2. **Create migration to add status columns**:
   ```php
   $table->string('general_status')->default('draft');
   $table->string('ocr_status')->nullable();
   $table->string('allocation_status')->nullable();
   $table->string('approval_status')->nullable();
   $table->string('delivery_status')->nullable();
   $table->string('payment_status')->nullable();
   ```

3. **Update all code to use specific status checks**:
   ```php
   // Instead of: $expense->status === InvoiceStatus::PROCESSING
   // Use: $expense->allocation_status === AllocationStatus::PENDING
   ```

4. **Implement the `InvoiceStatusService`** to manage status transitions across all dimensions.

## Next Steps

1. **Database Migration**: Add the new status columns to `invoices` and `expenses` tables
2. **Model Updates**: Update models to handle multiple status fields
3. **Logic Migration**: Gradually migrate business logic to use specific status enums
4. **API Updates**: Ensure API responses include the new status structure (camelCase format)
5. **Frontend Updates**: Update frontend to handle the new status architecture

## Migration Strategy

The migration should be done gradually:

1. **Phase 1**: ✅ Fix immediate errors (COMPLETED)
2. **Phase 2**: Add new status columns with default values
3. **Phase 3**: Populate existing records with appropriate status values
4. **Phase 4**: Update business logic to use new status fields
5. **Phase 5**: Remove the old `status` column and fully migrate to new architecture

This approach ensures zero downtime and maintains data integrity throughout the transition. 
