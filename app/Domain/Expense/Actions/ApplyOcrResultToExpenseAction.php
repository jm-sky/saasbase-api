<?php

namespace App\Domain\Expense\Actions;

use App\Domain\Common\Exceptions\UnsupportedProcessableTypeException;
use App\Domain\Common\Models\OcrRequest;
use App\Domain\Common\Models\VatRate;
use App\Domain\Expense\Models\Expense;
use App\Domain\Financial\DTOs\InvoiceLineDTO;
use App\Domain\Financial\DTOs\InvoicePaymentBankAccountDTO;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\PaymentMethod;
use App\Services\AzureDocumentIntelligence\DTOs\InvoiceDocumentDTO;
use App\Services\AzureDocumentIntelligence\DTOs\InvoiceDocumentItemDTO;
use Brick\Math\BigDecimal;
use Illuminate\Support\Str;

class ApplyOcrResultToExpenseAction
{
    /**
     * @throws UnsupportedProcessableTypeException
     */
    public function handle(OcrRequest $ocrRequest): void
    {
        $expense = $ocrRequest->processable;

        if (!$expense instanceof Expense) {
            throw new UnsupportedProcessableTypeException('Unsupported processable type');
        }

        /** @var InvoiceDocumentDTO $document */
        $document = $this->getDocument($ocrRequest);

        $this->mapDocumentToExpense($expense, $document);
    }

    public function mapDocumentToExpense(Expense $expense, InvoiceDocumentDTO $document): void
    {
        // Basic Invoice Information
        $expense->number     = $document->invoiceId?->value ?? $expense->number;
        $expense->issue_date = $document->invoiceDate?->getDate() ?? $expense->issue_date;

        // Financial Information
        $expense->total_net   = $document->subTotal?->getAmount() ?? $expense->total_net;
        $expense->total_tax   = $document->totalTax?->getAmount() ?? $expense->total_tax;
        $expense->total_gross = $document->invoiceTotal?->getAmount() ?? $expense->total_gross;

        // Seller Information (Vendor)
        $expense->seller->name    = $document->vendorName?->value ?? $expense->seller->name;
        $expense->seller->taxId   = $document->vendorTaxId?->value ?? $expense->seller->taxId;
        $expense->seller->address = $document->vendorAddress?->getFullAddress() ?? $expense->seller->address;
        $expense->seller->email   = $document->vendorContactEmail?->value ?? $expense->seller->email;
        $expense->seller->iban    = $document->paymentDetails[0]->iban ?? $expense->seller->iban;

        // Buyer Information (Customer)
        $expense->buyer->name    = $document->customerName?->value ?? $expense->buyer->name;
        $expense->buyer->taxId   = $document->customerTaxId?->value ?? $expense->buyer->taxId;
        $expense->buyer->address = $document->customerAddress?->getFullAddress() ?? $expense->buyer->address;
        $expense->buyer->email   = $document->customerContactEmail?->value ?? $expense->buyer->email;

        // Invoice Body
        $expense->body->description = $document->invoiceType?->value ?? $expense->body->description;

        // Payment Information
        $expense->payment->dueDate = $document->dueDate?->getDate() ?? $expense->payment->dueDate;
        $expense->payment->method  = PaymentMethod::tryFrom($document->paymentTerm?->value ?? '') ?? $expense->payment->method;
        $expense->payment->terms   = $document->paymentTerm?->value ?? $expense->payment->terms;

        $expense->payment->bankAccount        = $document->paymentDetails[0]?->bankAccount ?? new InvoicePaymentBankAccountDTO();
        $expense->payment->bankAccount->iban  = $document->paymentDetails[0]?->iban ?? $expense->payment->bankAccount->iban;
        $expense->payment->bankAccount->swift = $document->paymentDetails[0]?->swift ?? $expense->payment->bankAccount->swift;

        $expense->body->lines = collect($document->items)->map(function (InvoiceDocumentItemDTO $item) {
            return new InvoiceLineDTO(
                id: Str::ulid(),
                description: $item->description?->value,
                quantity: $item->quantity?->value,
                unitPrice: $item->unitPrice?->value,
                vatRate: VatRate::firstWhere('code', 'like', "%{$item->taxRate?->value}%")->toDTO() ?? VatRate::first()->toDTO(),
                totalNet: $item->totalPrice?->getAmount() ?? BigDecimal::zero(),
                totalVat: $item->tax?->getAmount() ?? BigDecimal::zero(),
                totalGross: $item->amount?->getAmount() ?? BigDecimal::zero(),
                productId: null,
            );
        })->toArray();

        if (0 === count($expense->body->lines)) {
            $expense->body->lines = [
                new InvoiceLineDTO(
                    id: Str::ulid(),
                    description: $document->invoiceType?->value ?? $document->invoiceSubCategory?->value ?? '',
                    quantity: BigDecimal::zero(),
                    unitPrice: BigDecimal::zero(),
                    vatRate: VatRate::first()->toDTO(),
                    totalNet: $document->invoiceTotal?->getAmount() ?? BigDecimal::zero(),
                    totalVat: BigDecimal::zero(),
                    totalGross: $document->invoiceTotal?->getAmount() ?? BigDecimal::zero(),
                    productId: null,
                ),
            ];
        }

        $expense->status = InvoiceStatus::OCR_COMPLETED;
        $expense->save();
    }

    protected function getDocument(OcrRequest $ocrRequest): InvoiceDocumentDTO
    {
        return $ocrRequest->result->analyzeResult->documents[0];
    }
}
