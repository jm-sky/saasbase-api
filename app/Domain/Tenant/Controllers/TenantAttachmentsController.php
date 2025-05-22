<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Common\DTOs\MediaDTO;
use App\Domain\Common\Models\Media;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Tenant\Enums\TenantActivityType;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\TenantAttachmentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;

class TenantAttachmentsController extends Controller
{
    use AuthorizesRequests;
    use HasActivityLogging;

    public function index(Tenant $tenant)
    {
        $this->authorize('viewAny', [Media::class, $tenant]);

        $media = $tenant->getMedia('attachments');

        return response()->json([
            'data' => MediaDTO::collection($media),
        ]);
    }

    public function store(TenantAttachmentRequest $request, Tenant $tenant)
    {
        $this->authorize('create', [Media::class, $tenant]);

        $file  = $request->file('file');
        $media = $tenant->addMedia($file)->toMediaCollection('attachments');
        $tenant->logModelActivity(TenantActivityType::AttachmentCreated->value, $media);

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ], Response::HTTP_CREATED);
    }

    public function show(Tenant $tenant, Media $media)
    {
        $this->authorize('view', [$media, $tenant]);

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ]);
    }

    public function download(Tenant $tenant, Media $media)
    {
        $this->authorize('view', [$media, $tenant]);

        $path    = $media->getPath();
        $headers = [
            'Content-Type'        => $media->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $media->file_name . '"',
        ];

        return response()->download($path, $media->file_name, $headers);
    }

    public function preview(Tenant $tenant, Media $media)
    {
        $this->authorize('view', [$media, $tenant]);

        $path    = $media->getPath();
        $headers = [
            'Content-Type'        => $media->mime_type,
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        ];

        return response()->file($path, $headers);
    }

    public function destroy(Tenant $tenant, Media $media)
    {
        $this->authorize('delete', [$media, $tenant]);
        $tenant->logModelActivity(TenantActivityType::AttachmentDeleted->value, $media);
        $media->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
