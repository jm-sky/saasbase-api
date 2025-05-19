<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Tenant\Enums\TenantActivityType;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\TenantLogoUploadRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TenantLogoController extends Controller
{
    public function upload(TenantLogoUploadRequest $request, Tenant $tenant)
    {
        $tenant->clearMediaCollection('logo');

        $media = $tenant->addMediaFromRequest('image')
            ->toMediaCollection('logo')
        ;

        activity()
            ->performedOn($tenant)
            ->withProperties([
                'tenant_id' => $tenant->id,
                'logo_id'   => $media->id,
            ])
            ->event(TenantActivityType::LogoCreated->value)
            ->log('Tenant logo created')
        ;

        $logoUrl  = $tenant->getMediaSignedUrl('logo');
        $thumbUrl = $tenant->getMediaSignedUrl('logo', 'thumb');

        return response()->json([
            'message'     => 'Tenant logo uploaded successfully.',
            'originalUrl' => $logoUrl,
            'thumbUrl'    => $thumbUrl,
        ]);
    }

    public function show(Tenant $tenant, Request $request)
    {
        $thumb = $request->query('thumb', false);
        $media = $thumb ? $tenant->getFirstMedia('logo', 'thumb') : $tenant->getFirstMedia('logo');

        if ($thumb && !$media) {
            $media = $tenant->getFirstMedia('logo');
        }

        if (!$media) {
            return response()->json(['message' => 'No logo found.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $stream = Storage::disk($media->disk)->readStream($media->getPath());

        return Response::stream(function () use ($stream) {
            fpassthru($stream);
        }, HttpResponse::HTTP_OK, [
            'Content-Type'        => $media->mime_type,
            'Content-Length'      => $media->size,
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        ]);
    }

    public function delete(Tenant $tenant)
    {
        $media = $tenant->getFirstMedia('logo');
        $tenant->clearMediaCollection('logo');

        if ($media) {
            activity()
                ->performedOn($tenant)
                ->withProperties([
                    'tenant_id' => $tenant->id,
                    'logo_id'   => $media->id,
                ])
                ->event(TenantActivityType::LogoDeleted->value)
                ->log('Tenant logo deleted')
            ;
        }

        return response()->json(['message' => 'Tenant logo deleted.'], HttpResponse::HTTP_NO_CONTENT);
    }
}
