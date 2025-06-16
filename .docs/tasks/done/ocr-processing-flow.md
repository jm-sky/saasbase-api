## Task: Implement OCR Processing Flow for Uploaded Expense Files

We need to support an OCR processing flow for uploaded expense attachments using Azure Document Intelligence.

### 🧩 Context

Users can drag & drop one or more files onto the expense page. This opens a modal: "Add costs for OCR".

When confirmed:
- For **each file**, create a new `Expense` record with minimal info and `status = 'ocr_processing'`
- Attach the uploaded file using **Spatie Media Library**
- Create an `OcrRequest` record to track the OCR process

Later, a queue job will trigger OCR via Azure Document Intelligence and update the Expense with the parsed data.

---

### 📄 New model: `OcrRequest`

This model stores metadata about the OCR process and should support polymorphic relation (`Expense`, `Invoice`, `Document`, etc.).

```php
// database structure (simplified)

- id: ulid (primary key)
- processable_type: string       // polymorphic
- processable_id: ulid
- media_id: ulid                 // ID of the related media (Spatie)
- external_document_id: string|null   // ID from Azure Document Intelligence
- status: string                 // one of: pending, processing, completed, failed
- result: jsonb|null            // raw OCR result (JSON from Azure)
- errors: jsonb|null            // errors from OCR
- started_at: datetime|null
- finished_at: datetime|null
- created_by: ulid              // user who initiated the upload
- created_at, updated_at
```

#### Relationships:
- `morphTo('processable')`
- `belongsTo('media')`
- `belongsTo('createdBy', User::class)`

#### Status enum:
- `pending`
- `processing`
- `completed`
- `failed`

---

### 🔁 Flow Summary

1. **Frontend (drag & drop upload)**
   - Send files to API endpoint (e.g., `/expenses/ocr-upload`)
   - For each file:
     - Create new `Expense` with `status = 'ocr_processing'`
     - Attach media
     - Create `OcrRequest` linked to the expense and media
     - Dispatch job: `StartOcrJob`

2. **`StartOcrJob`**
   - Sends file to Azure Document Intelligence
   - Updates `OcrRequest` with `external_document_id`, `started_at`, and sets `status = processing`

3. **When OCR result is ready**
   - Process result and update `OcrRequest` (`status = completed`, `result`, `finished_at`)
   - Update `Expense` with parsed data (amount, vendor, date, etc.)
   - Optionally: update `status = 'draft'` (or other InvoiceStatus) on `Expense`
   - **Broadcast event** to notify frontend (via Soketi or Laravel Echo) — use `created_by` for channel scoping.

---

### ✅ Deliverables

- Migration + model: `OcrRequest`
- Backend endpoint to handle file upload and initialize OCR flow
- `StartOcrJob` to call Azure OCR and update request
- `FinishOcrJob` (or listener) to store result and update related `Expense`
- Broadcast final result to the user via WebSocket

---

### Job draft 

```php
<?php

namespace App\Domain\Common\Jobs;

use App\Domain\Common\Models\OcrRequest;
use App\Services\AzureDocumentIntelligence\DocumentAnalysisService;
use App\Services\AzureDocumentIntelligence\Exceptions\AzureDocumentIntelligenceException;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class StartOcrJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public OcrRequest $ocrRequest;

    public int $tries = 5;

    public int $backoff = 10;

    public function __construct(OcrRequest $ocrRequest)
    {
        $this->ocrRequest = $ocrRequest;
    }

    public function handle(DocumentAnalysisService $azure): void
    {
        $this->ocrRequest->update([
            'status'     => 'processing', // use enum 
            'started_at' => now(),
        ]);

        try {
            $media = $this->ocrRequest->media;

            if (!$media) {
                throw new Exception("Media not found for OCR request ID {$this->ocrRequest->id}");
            }

            // Generate a temporary S3 URL (valid e.g. 5 minutes)
            $temporaryUrl = $media->getTemporaryUrl(now()->addMinutes(5)); // extract time to const 

            // Analyze document
            // TODO: Implement analysis by url
            $result = $azure->analyzeByUrl($temporaryUrl);

            $this->ocrRequest->update([
                'status'               => 'completed', // use enum 
                'result'               => $result->toArray(),
                'external_document_id' => $result->documentId ?? null,
                'finished_at'          => now(),
            ]);
        } catch (AzureDocumentIntelligenceException $ex) {
            $this->failWithReason('Azure error', $ex);
        } catch (Exception $ex) {
            $this->failWithReason('Unexpected error', $ex);
        }
    }

    protected function failWithReason(string $message, Exception $ex): void
    {
        Log::error("[OCR] {$message}: {$ex->getMessage()}", [
            'ocr_request_id' => $this->ocrRequest->id,
            'exception'      => $ex,
        ]);

        $this->ocrRequest->update([
            'status'      => 'failed', // use enum
            'errors'      => ['message' => $ex->getMessage()],
            'finished_at' => now(),
        ]);

        $this->fail($ex);
    }
}
```

---

## Last step, draft 

```
<?php

namespace App\Domain\Expense\Jobs;

use App\Domain\Expense\Models\Expense;
use App\Domain\Common\Models\OcrRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;

class FinishOcrJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public OcrRequest $ocrRequest;

    public function __construct(OcrRequest $ocrRequest)
    {
        $this->ocrRequest = $ocrRequest;
    }

    public function handle(): void
    {
        $result = $this->ocrRequest->result;

        if (!is_array($result)) {
            Log::warning("[OCR] Invalid result format", ['id' => $this->ocrRequest->id]);
            return;
        }

        /** @var Expense $expense */
        $expense = $this->ocrRequest->processable;

        if (!$expense instanceof Expense) {
            Log::warning("[OCR] Unsupported processable type", ['type' => get_class($expense)]);
            return;
        }

        // 🔍 Map result fields (this will depend on your Azure model)
        $parsed = $this->parseAzureResult($result);

        // ✅ Update expense
        $expense->update([
            'vendor_name' => $parsed['vendor_name'] ?? null,
            'issued_at'   => $parsed['issued_at'] ?? null,
            'amount'      => $parsed['amount'] ?? null,
            'status'      => 'draft',
        ]);

        // 📢 Notify user
        broadcast(new \App\Events\OcrExpenseCompleted(
            user: $this->ocrRequest->createdBy,
            expense: $expense
        ));
    }

    protected function parseAzureResult(array $result): array
    {
        // This should map Azure JSON to your internal format
        // Example for prebuilt invoice model:
        return [
            'vendor_name' => $result['vendorName']['content'] ?? null,
            'issued_at'   => $result['invoiceDate']['valueDate'] ?? null,
            'amount'      => $result['totalAmount']['value'] ?? null,
        ];
    }
}
```