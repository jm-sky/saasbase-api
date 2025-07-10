<?php

namespace App\Domain\Invoice\Controllers;

use App\Domain\Common\DTOs\MediaDTO;
use App\Domain\Common\Models\Media;
use App\Domain\Common\Resources\MediaResource;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Invoice\Enums\InvoiceActivityType;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Invoice\Requests\InvoiceAttachmentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class InvoiceAttachmentsController extends Controller
{
    use HasActivityLogging;

    /**
     * List all attachments for a invoice.
     */
    public function index(Invoice $invoice)
    {
        $media = $invoice->getMedia('attachments');

        $ocrMediaId = $invoice->ocrRequest?->media_id;

        $media = $media->map(function (Media $media) use ($ocrMediaId): Media {
            if ($media->id === $ocrMediaId) {
                $meta          = $media->meta ?? [];
                $meta['isOcr'] = true;
                $media->meta   = $meta;
            }

            return $media;
        });

        return MediaResource::collection($media);
    }

    /**
     * Upload a new attachment.
     */
    public function store(InvoiceAttachmentRequest $request, Invoice $invoice)
    {
        $file  = $request->file('file');
        $media = $invoice->addMedia($file)->toMediaCollection('attachments');
        $invoice->logModelActivity(InvoiceActivityType::AttachmentCreated->value, $media);

        return response()->json([
            'message' => 'Attachment uploaded successfully.',
            'data'    => MediaDTO::fromModel($media)->toArray(),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show metadata for a single attachment.
     */
    public function show(Invoice $invoice, Media $media)
    {
        $this->authorizeMedia($invoice, $media);

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ]);
    }

    /**
     * Download an attachment.
     */
    public function download(Invoice $invoice, Media $media)
    {
        $this->authorizeMedia($invoice, $media);

        $disk = Storage::disk($media->disk);
        $path = $media->getPathRelativeToRoot();

        if (!$disk->exists($path)) {
            abort(Response::HTTP_NOT_FOUND, 'File not found.');
        }

        $headers = [
            'Content-Type'        => $media->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $media->file_name . '"',
        ];

        return response()->streamDownload(function () use ($disk, $path) {
            echo $disk->get($path);
        }, $media->file_name, $headers);
    }

    /**
     * Preview an attachment (inline).
     */
    public function preview(Invoice $invoice, Media $media)
    {
        $this->authorizeMedia($invoice, $media);

        $disk = Storage::disk($media->disk);
        $path = $media->getPathRelativeToRoot();

        if (!$disk->exists($path)) {
            abort(Response::HTTP_NOT_FOUND, 'File not found.');
        }

        $headers = [
            'Content-Type'        => $media->mime_type,
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        ];

        return response()->streamDownload(function () use ($disk, $path) {
            echo $disk->get($path);
        }, $media->file_name, $headers);
    }

    /**
     * Delete an attachment.
     */
    public function destroy(Invoice $invoice, $mediaId)
    {
        $media = Media::findOrFail($mediaId);
        $this->authorizeMedia($invoice, $media);
        $invoice->logModelActivity(InvoiceActivityType::AttachmentDeleted->value, $media);
        $media->delete();

        return response()->json(['message' => 'Attachment deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

    /**
     * Ensure the media belongs to the invoice.
     */
    protected function authorizeMedia(Invoice $invoice, Media $media): void
    {
        if (Invoice::class !== $media->model_type || $media->model_id !== $invoice->id) {
            abort(Response::HTTP_NOT_FOUND, 'Attachment not found for this invoice.');
        }
    }
}
