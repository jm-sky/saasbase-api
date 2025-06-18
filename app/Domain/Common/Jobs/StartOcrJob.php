<?php

namespace App\Domain\Common\Jobs;

use App\Domain\Common\Enums\OcrRequestStatus;
use App\Domain\Common\Models\Media;
use App\Domain\Common\Models\OcrRequest;
use App\Domain\Expense\Jobs\FinishOcrJob;
use App\Helpers\FileNames;
use App\Services\AzureDocumentIntelligence\DocumentAnalysisService;
use App\Services\AzureDocumentIntelligence\Exceptions\AzureDocumentIntelligenceException;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StartOcrJob implements ShouldQueue, ShouldBeUnique
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public int $backoff = 10;

    public OcrRequest $ocrRequest;

    protected string $temporaryFilePath;

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

            if (config('filesystems.use_s3_temporary_urls')) {
                // Generate a temporary S3 URL (valid e.g. 5 minutes)
                $temporaryUrl = $media->getTemporaryUrl(now()->addMinutes($temporaryUrlTtl));
            } else {
                // Save to disk and use local filepath
                $temporaryUrl = $this->saveMediaToDisk($media);
            }

            // Analyze document
            $result = $azure->analyze($temporaryUrl);

            $this->deleteProcessedMediaFile();

            $this->ocrRequest->update([
                'status'               => OcrRequestStatus::Completed,
                'result'               => $result,
                'external_document_id' => $result->documentId ?? null,
                'finished_at'          => now(),
            ]);

            FinishOcrJob::dispatch($this->ocrRequest);
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

    protected function saveMediaToDisk(Media $media): string
    {
        $extension = FileNames::getExtensionFromMimeType($media->mime_type);

        // Generate a unique filename
        $filename = "{$media->id}.{$extension}";

        // Get temporary URL and download the file
        $temporaryUrl = $media->getTemporaryUrl(now()->addMinutes(5));
        $fileContent  = file_get_contents($temporaryUrl);

        Storage::disk('local')->put("ocr-temp/{$filename}", $fileContent);

        $this->temporaryFilePath = Storage::disk('local')->path("ocr-temp/{$filename}");

        return $this->temporaryFilePath;
    }

    protected function deleteProcessedMediaFile(): void
    {
        if ($this->temporaryFilePath) {
            unlink($this->temporaryFilePath);
        }
    }
}
