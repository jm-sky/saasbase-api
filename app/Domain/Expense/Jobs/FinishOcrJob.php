<?php

namespace App\Domain\Expense\Jobs;

use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Common\Models\OcrRequest;
use App\Domain\Expense\Events\OcrExpenseCompleted;
use App\Domain\Expense\Models\Expense;
use App\Services\AzureDocumentIntelligence\DTOs\Document;
use App\Services\AzureDocumentIntelligence\DTOs\DocumentAnalysisResult;
use Brick\Math\BigDecimal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
            /** @var Document $document */
            $document             = $result->analyzeResult->documents[0];
            $expense->total_net   = $document->fields['totalNet'] ? BigDecimal::of($document->fields['totalNet']) : $expense->total_net;
            $expense->total_tax   = $document->fields['totalTax'] ? BigDecimal::of($document->fields['totalTax']) : $expense->total_tax;
            $expense->total_gross = $document->fields['totalGross'] ? BigDecimal::of($document->fields['totalGross']) : $expense->total_gross;
            // $expense->seller = $result->seller ?? $expense->seller;
            // $expense->buyer = $result->buyer ?? $expense->buyer;
            // $expense->body = $result->body ?? $expense->body;
            // $expense->payment = $result->payment ?? $expense->payment;

            $expense->save();

            broadcast(new OcrExpenseCompleted(
                notifiable: $this->ocrRequest->createdBy,
                expense: $expense
            ));
        } catch (\Exception $e) {
            Log::error('[OCR] Error finishing OCR job', ['id' => $this->ocrRequest->id, 'error' => $e->getMessage()]);

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
