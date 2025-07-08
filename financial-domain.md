# Financial, Expense, and Invoice Domain Structure

```
app/Domain/
├── Financial/
│   ├── Enums/
│   │   ├── VatRateType.php
│   │   ├── PaymentMethod.php
│   │   ├── AllocationStatus.php
│   │   ├── ApprovalStatus.php
│   │   ├── DeliveryStatus.php
│   │   ├── InvoiceStatus.php
│   │   ├── PaymentStatus.php
│   │   ├── InvoiceType.php
│   │   └── ResetPeriod.php
│   ├── Controllers/
│   │   ├── VatRateController.php
│   │   ├── FinancialReportController.php
│   │   └── PaymentMethodController.php
│   ├── Models/
│   │   ├── VatRate.php
│   │   ├── AllocationTransactionType.php
│   │   ├── AllocationCostType.php
│   │   ├── AllocationRelatedTransactionCategory.php
│   │   ├── AllocationRevenueType.php
│   │   └── PaymentMethod.php
│   ├── DTOs/
│   │   ├── VatRateDTO.php
│   │   ├── InvoiceBodyDTO.php
│   │   ├── InvoiceStatusDTO.php
│   │   ├── InvoiceLineDTO.php
│   │   ├── PaymentMethodDTO.php
│   │   ├── InvoicePaymentBankAccountDTO.php
│   │   ├── InvoicePaymentDTO.php
│   │   ├── InvoiceDTO.php
│   │   ├── InvoiceVatSummaryDTO.php
│   │   ├── InvoiceOptionsDTO.php
│   │   ├── InvoicePartyDTO.php
│   │   └── InvoiceExchangeDTO.php
│   ├── Examples/
│   │   └── StatusArchitectureExample.php
│   ├── Resources/
│   │   ├── FinancialBalanceWidgetResource.php
│   │   ├── FinancialExpensesWidgetResource.php
│   │   ├── FinancialOverviewWidgetResource.php
│   │   ├── FinancialRevenueWidgetResource.php
│   │   ├── FinancialWidgetMetricsResource.php
│   │   └── PaymentMethodResource.php
│   ├── Services/
│   │   └── InvoiceStatusService.php
│   ├── Requests/
│   │   ├── StorePaymentMethodRequest.php
│   │   └── UpdatePaymentMethodRequest.php
│   └── Casts/
│       ├── InvoicePartyCast.php
│       ├── InvoiceBodyCast.php
│       ├── InvoiceOptionsCast.php
│       ├── InvoicePaymentCast.php
│       └── BigDecimalCast.php
├── Expense/
│   ├── Resources/
│   │   ├── ApprovalDecisionResource.php
│   │   ├── ApprovalStepApproverResource.php
│   │   ├── ApprovalExecutionResource.php
│   │   ├── PendingApprovalsResource.php
│   │   ├── ApprovalUserResource.php
│   │   ├── ApprovalWorkflowStepResource.php
│   │   ├── ApprovalWorkflowResource.php
│   │   ├── AllocationDimensionResource.php
│   │   ├── AllocationSuggestionsResource.php
│   │   ├── DimensionConfigurationResource.php
│   │   ├── DimensionDataResource.php
│   │   ├── DimensionItemResource.php
│   │   ├── DimensionTypeResource.php
│   │   ├── ExpenseAllocationResource.php
│   │   ├── ExpenseAllocationSummaryResource.php
│   │   └── ExpenseResource.php
│   ├── Requests/
│   │   ├── ProcessApprovalDecisionRequest.php
│   │   ├── AutoAllocateExpenseRequest.php
│   │   ├── StoreExpenseAllocationRequest.php
│   │   ├── StoreExpenseRequest.php
│   │   ├── UpdateDimensionConfigurationRequest.php
│   │   ├── ExpenseAttachmentRequest.php
│   │   ├── UploadExpenseOcrRequest.php
│   │   └── UpdateExpenseRequest.php
│   ├── Controllers/
│   │   ├── ExpenseAttachmentsController.php
│   │   ├── ExpenseApprovalController.php
│   │   ├── DimensionConfigurationController.php
│   │   ├── ExpenseAllocationController.php
│   │   └── ExpenseController.php
│   ├── Models/
│   │   ├── AllocationDimension.php
│   │   ├── Expense.php
│   │   ├── ExpenseAllocation.php
│   │   └── TenantDimensionConfiguration.php
│   ├── Actions/
│   │   ├── ApplyOcrResultToExpenseAction.php
│   │   ├── AllocateExpenseAction.php
│   │   └── CreateExpenseForOcr.php
│   ├── Contracts/
│   │   ├── AllocationDimensionInterface.php
│   │   └── README.md
│   ├── DTOs/
│   │   ├── AllocationDataDTO.php
│   │   ├── AllocationDimensionDTO.php
│   │   ├── DimensionConfigurationDTO.php
│   │   └── DimensionDataDTO.php
│   ├── Enums/
│   │   ├── AllocationDimensionType.php
│   │   ├── ExpenseAllocationStatus.php
│   │   └── ExpenseActivityType.php
│   ├── Jobs/
│   │   └── FinishOcrJob.php
│   ├── Services/
│   │   └── DimensionVisibilityService.php
│   ├── Traits/
│   │   └── HasAllocationDimensionInterface.php
│   └── Events/
│       └── OcrExpenseCompleted.php
└── Invoice/
    ├── Requests/
    │   ├── InvoicePdfRequest.php
    │   ├── InvoiceAttachmentRequest.php
    │   ├── StoreInvoiceRequest.php
    │   ├── UpdateNumberingTemplateRequest.php
    │   └── UpdateInvoiceRequest.php
    ├── Enums/
    │   ├── InvoiceActivityType.php
    │   └── ResetPeriod.php
    ├── Controllers/
    │   ├── InvoiceController.php
    │   ├── InvoiceAttachmentsController.php
    │   ├── InvoiceShareTokenController.php
    │   └── NumberingTemplateController.php
    ├── Models/
    │   ├── Invoice.php
    │   └── NumberingTemplate.php
    └── Resources/
        ├── InvoiceResource.php
        ├── NumberingTemplateResource.php
        └── StoreInvoiceShareTokenRequest.php
```
