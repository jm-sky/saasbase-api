<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Requests\ContractorLogoUploadRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ContractorLogoController extends Controller
{
    use HasActivityLogging;

    public function upload(ContractorLogoUploadRequest $request, Contractor $contractor)
    {
        $contractor->clearMediaCollection('logo');

        $media = $contractor->addMediaFromRequest('image')->toMediaCollection('logo');

        $contractor->logModelActivity(ContractorActivityType::LogoCreated->value, $media);

        $logoUrl  = $contractor->getMediaSignedUrl('logo');
        $thumbUrl = $contractor->getMediaSignedUrl('logo', 'thumb');

        return response()->json([
            'message'     => 'Contractor logo uploaded successfully.',
            'originalUrl' => $logoUrl,
            'thumbUrl'    => $thumbUrl,
        ]);
    }

    public function show(Contractor $contractor, Request $request)
    {
        $thumb = $request->query('thumb', false);
        $media = $thumb ? $contractor->getFirstMedia('logo', 'thumb') : $contractor->getFirstMedia('logo');

        if ($thumb && !$media) {
            $media = $contractor->getFirstMedia('logo');
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

    public function delete(Contractor $contractor)
    {
        $media = $contractor->getFirstMedia('logo');
        $contractor->clearMediaCollection('logo');

        if ($media) {
            $contractor->logModelActivity(ContractorActivityType::LogoDeleted->value, $media);
        }

        return response()->json(['message' => 'Contractor logo deleted.'], HttpResponse::HTTP_NO_CONTENT);
    }
}
