<?php

namespace App\Domain\Expense\Controllers;

use App\Domain\Common\DTOs\MediaDTO;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Expense\Actions\CreateExpenseForOcr;
use App\Domain\Expense\Enums\ExpenseActivityType;
use App\Domain\Expense\Models\Expense;
use App\Domain\Expense\Requests\ExpenseAttachmentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ExpenseAttachmentsController extends Controller
{
    use HasActivityLogging;

    /**
     * List all attachments for a expense.
     */
    public function index(Expense $expense)
    {
        $media = $expense->getMedia('attachments');

        return response()->json([
            'data' => MediaDTO::collection($media),
        ]);
    }

    /**
     * Upload a new attachment.
     */
    public function store(ExpenseAttachmentRequest $request, Expense $expense)
    {
        $file  = $request->file('file');
        $media = $expense->addMedia($file)->toMediaCollection('attachments');
        $expense->logModelActivity(ExpenseActivityType::AttachmentCreated->value, $media);

        if (!$expense->ocrRequest) {
            CreateExpenseForOcr::createOcrRequest($expense, $media);
        }

        return response()->json([
            'message' => 'Attachment uploaded successfully.',
            'data'    => MediaDTO::fromModel($media)->toArray(),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show metadata for a single attachment.
     */
    public function show(Expense $expense, Media $media)
    {
        $this->authorizeMedia($expense, $media);

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ]);
    }

    /**
     * Download an attachment.
     */
    public function download(Expense $expense, Media $media)
    {
        $this->authorizeMedia($expense, $media);
        $disk    = $media->disk;
        $path    = $media->getPath();
        $headers = [
            'Content-Type'        => $media->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $media->file_name . '"',
        ];

        return response()->download($path, $media->file_name, $headers);
    }

    /**
     * Preview an attachment (inline).
     */
    public function preview(Expense $expense, Media $media)
    {
        $this->authorizeMedia($expense, $media);
        $disk    = $media->disk;
        $path    = $media->getPath();
        $headers = [
            'Content-Type'        => $media->mime_type,
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        ];

        return response()->file($path, $headers);
    }

    /**
     * Delete an attachment.
     */
    public function destroy(Expense $expense, $mediaUuid)
    {
        $media = Media::findByUuid($mediaUuid);
        $this->authorizeMedia($expense, $media);
        $expense->logModelActivity(ExpenseActivityType::AttachmentDeleted->value, $media);
        $media->delete();

        return response()->json(['message' => 'Attachment deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

    /**
     * Ensure the media belongs to the expense.
     */
    protected function authorizeMedia(Expense $expense, Media $media): void
    {
        if (Expense::class !== $media->model_type || $media->model_id !== $expense->id) {
            abort(Response::HTTP_NOT_FOUND, 'Attachment not found for this expense.');
        }
    }
}
