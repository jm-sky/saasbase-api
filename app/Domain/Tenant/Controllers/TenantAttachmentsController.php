<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Common\DTOs\MediaDTO;
use App\Domain\Tenant\Enums\TenantActivityType;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\TenantAttachmentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TenantAttachmentsController extends Controller
{
    public function index(Tenant $tenant)
    {
        $media = $tenant->getMedia('attachments');

        return response()->json([
            'data' => MediaDTO::collection($media),
        ]);
    }

    public function store(TenantAttachmentRequest $request, Tenant $tenant)
    {
        $file  = $request->file('file');
        $media = $tenant->addMedia($file)->toMediaCollection('attachments');

        activity()
            ->performedOn($tenant)
            ->withProperties([
                'tenant_id'     => $tenant->id,
                'attachment_id' => $media->id,
            ])
            ->event(TenantActivityType::AttachmentCreated->value)
            ->log('Tenant attachment created')
        ;

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ], Response::HTTP_CREATED);
    }

    public function show(Tenant $tenant, Media $media)
    {
        $this->authorizeMedia($tenant, $media);

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ]);
    }

    public function download(Tenant $tenant, Media $media)
    {
        $this->authorizeMedia($tenant, $media);
        $path    = $media->getPath();
        $headers = [
            'Content-Type'        => $media->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $media->file_name . '"',
        ];

        return response()->download($path, $media->file_name, $headers);
    }

    public function preview(Tenant $tenant, Media $media)
    {
        $this->authorizeMedia($tenant, $media);
        $path    = $media->getPath();
        $headers = [
            'Content-Type'        => $media->mime_type,
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        ];

        return response()->file($path, $headers);
    }

    public function destroy(Tenant $tenant, Media $media)
    {
        $this->authorizeMedia($tenant, $media);

        activity()
            ->performedOn($tenant)
            ->withProperties([
                'tenant_id'     => $tenant->id,
                'attachment_id' => $media->id,
            ])
            ->event(TenantActivityType::AttachmentDeleted->value)
            ->log('Tenant attachment deleted')
        ;

        $media->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    protected function authorizeMedia(Tenant $tenant, Media $media): void
    {
        if (Tenant::class !== $media->model_type || $media->model_id !== $tenant->id) {
            abort(Response::HTTP_NOT_FOUND, 'Attachment not found for this tenant.');
        }
    }
}
