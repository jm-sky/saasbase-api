<?php

namespace App\Domain\Common\Jobs;

use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Common\Models\OcrRequest;
use App\Services\AzureDocumentIntelligence\DocumentAnalysisService;
use App\Services\AzureDocumentIntelligence\Exceptions\AzureDocumentIntelligenceException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StartOcrJob implements ShouldQueue
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

    public function handle(DocumentAnalysisService $azure): void
    {
        $temporaryUrlTtl = 5;

        $this->ocrRequest->update([
            'status'     => OcrRequestStatus::Processing,
            'started_at' => now(),
        ]);

        try {
            $media = $this->ocrRequest->media;

            if (!$media) {
                throw new \Exception("Media not found for OCR request ID {$this->ocrRequest->id}");
            }

            // Generate a temporary S3 URL (valid e.g. 5 minutes)
            $temporaryUrl = $media->getTemporaryUrl(now()->addMinutes($temporaryUrlTtl));

            // Analyze document
            $result = $azure->analyzeByUrlInternal($temporaryUrl);

            $this->ocrRequest->update([
                'status'               => OcrRequestStatus::Completed,
                'result'               => $result->toArray(),
                'external_document_id' => $result->documentId ?? null,
                'finished_at'          => now(),
            ]);
        } catch (AzureDocumentIntelligenceException $ex) {
            $this->failWithReason('Azure error', $ex);
        } catch (\Exception $ex) {
            $this->failWithReason('Unexpected error', $ex);
        }
    }

    protected function failWithReason(string $message, \Exception $ex): void
    {
        Log::error("[OCR] {$message}: {$ex->getMessage()}", [
            'ocr_request_id' => $this->ocrRequest->id,
            'exception'      => $ex,
        ]);

        $this->ocrRequest->update([
            'status'      => OcrRequestStatus::Failed,
            'errors'      => ['message' => $ex->getMessage()],
            'finished_at' => now(),
        ]);

        $this->fail($ex);
    }
}
