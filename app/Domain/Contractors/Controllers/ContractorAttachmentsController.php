<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\DTOs\MediaDTO;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Requests\ContractorAttachmentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ContractorAttachmentsController extends Controller
{
    /**
     * List all attachments for a contractor.
     */
    public function index(Contractor $contractor)
    {
        $media = $contractor->getMedia('attachments');

        return response()->json([
            'data' => MediaDTO::collection($media),
        ]);
    }

    /**
     * Upload a new attachment.
     */
    public function store(ContractorAttachmentRequest $request, Contractor $contractor)
    {
        $file  = $request->file('file');
        $media = $contractor->addMedia($file)->toMediaCollection('attachments');

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show metadata for a single attachment.
     */
    public function show(Contractor $contractor, Media $media)
    {
        $this->authorizeMedia($contractor, $media);

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ]);
    }

    /**
     * Download an attachment.
     */
    public function download(Contractor $contractor, Media $media)
    {
        $this->authorizeMedia($contractor, $media);
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
    public function preview(Contractor $contractor, Media $media)
    {
        $this->authorizeMedia($contractor, $media);
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
    public function destroy(Contractor $contractor, Media $media)
    {
        $this->authorizeMedia($contractor, $media);
        $media->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Ensure the media belongs to the contractor.
     */
    protected function authorizeMedia(Contractor $contractor, Media $media): void
    {
        if (Contractor::class !== $media->model_type || $media->model_id !== $contractor->id) {
            abort(Response::HTTP_NOT_FOUND, 'Attachment not found for this contractor.');
        }
    }
}
