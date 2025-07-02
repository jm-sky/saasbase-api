<?php

namespace App\Domain\Expense\Jobs;

use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Common\Models\OcrRequest;
use App\Domain\Expense\Actions\ApplyOcrResultToExpenseAction;
use App\Domain\Expense\Events\OcrExpenseCompleted;
use App\Domain\Financial\Enums\InvoiceStatus;
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

    public function __construct(
        OcrRequest $ocrRequest,
    ) {
        $this->ocrRequest = $ocrRequest;
    }

    public function handle(): void
    {
        try {
            if ($this->ocrRequest->hasNoDocument()) {
                Log::warning('[OCR] Invalid result format', ['id' => $this->ocrRequest->id]);

                return;
            }

            app(ApplyOcrResultToExpenseAction::class)->handle($this->ocrRequest);

            broadcast(new OcrExpenseCompleted(
                user: $this->ocrRequest->createdBy,
                expense: $this->ocrRequest->processable
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

        $this->ocrRequest->processable->update([
            'general_status' => InvoiceStatus::DRAFT,
        ]);

        $this->fail($ex);
    }
}
