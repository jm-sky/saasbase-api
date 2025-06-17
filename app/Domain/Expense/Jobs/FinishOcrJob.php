<?php

namespace App\Domain\Expense\Jobs;

use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Common\Models\OcrRequest;
use App\Domain\Expense\Events\OcrExpenseCompleted;
use App\Domain\Expense\Models\Expense;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Services\AzureDocumentIntelligence\DTOs\DocumentAnalysisResult;
use App\Services\AzureDocumentIntelligence\DTOs\InvoiceDocumentDTO;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FinishOcrJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public int $backoff = 10;

    public OcrRequest $ocrRequest;

    public function __construct(OcrRequest $ocrRequest)
    {
        $this->ocrRequest = $ocrRequest;
    }

    public function handle(): void
    {
        try {
            /** @var ?DocumentAnalysisResult $result */
            $result = $this->ocrRequest->result;

            if (!$result) {
                Log::warning('[OCR] Invalid result format', ['id' => $this->ocrRequest->id]);

                return;
            }

            /** @var Expense $expense */
            $expense = $this->ocrRequest->processable;

            if (!$expense instanceof Expense) {
                Log::warning('[OCR] Unsupported processable type', ['type' => get_class($expense)]);

                return;
            }

            // TODO: Make  document fields better typed
            // TODO: Implement seller, buyer, body, payment
            /** @var InvoiceDocumentDTO $document */
            $document                 = $result->analyzeResult->documents[0];
            $expense->total_net       = $document->subTotal?->getAmount() ?? $expense->total_net;
            $expense->total_tax       = $document->totalTax?->getAmount() ?? $expense->total_tax;
            $expense->total_gross     = $document->invoiceTotal?->getAmount() ?? $expense->total_gross;
            $expense->seller->name    = $document->vendorName?->value ?? $expense->seller->name;
            $expense->seller->taxId   = $document->vendorTaxId?->value ?? $expense->seller->taxId;
            $expense->seller->address = $document->vendorAddress?->getFullAddress() ?? $expense->seller->address;

            $expense->buyer->name    = $document->customerName?->value ?? $expense->buyer->name;
            $expense->buyer->taxId   = $document->customerTaxId?->value ?? $expense->buyer->taxId;
            $expense->buyer->address = $document->customerAddress?->getFullAddress() ?? $expense->buyer->address;

            $expense->body->description = $document->invoiceType?->value ?? $expense->body->description;
            // $expense->payment = $result->payment ?? $expense->payment;

            $expense->status = InvoiceStatus::OCR_COMPLETED;
            $expense->save();

            broadcast(new OcrExpenseCompleted(
                notifiable: $this->ocrRequest->createdBy,
                expense: $expense
            ));
        } catch (\Exception $e) {
            Log::error('[OCR] Error finishing OCR job', ['id' => $this->ocrRequest->id, 'error' => $e->getMessage()]);
            $expense         = $this->ocrRequest->processable;
            $expense->status = InvoiceStatus::OCR_FAILED;
            $expense->save();

            $this->failWithReason('Error finishing OCR job', $e);
        }
    }

    protected function failWithReason(string $message, \Exception $ex): void
    {
        $this->ocrRequest->update([
            'status'      => OcrRequestStatus::Failed,
            'errors'      => ['message' => $ex->getMessage()],
            'finished_at' => now(),
        ]);

        $this->fail($ex);
    }
}
