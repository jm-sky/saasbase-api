<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Products\Enums\ProductActivityType;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Requests\ProductLogoUploadRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ProductLogoController extends Controller
{
    use HasActivityLogging;

    public function upload(ProductLogoUploadRequest $request, Product $product)
    {
        $product->clearMediaCollection('logo');

        $media = $product->addMediaFromRequest('image')
            ->toMediaCollection('logo')
        ;

        $product->logModelActivity(ProductActivityType::LogoCreated->value, $media);

        $logoUrl  = $product->getMediaSignedUrl('logo');
        $thumbUrl = $product->getMediaSignedUrl('logo', 'thumb');

        return response()->json([
            'message'     => 'Product logo uploaded successfully.',
            'originalUrl' => $logoUrl,
            'thumbUrl'    => $thumbUrl,
        ]);
    }

    public function show(Product $product, Request $request)
    {
        $thumb = $request->query('thumb', false);
        $media = $thumb ? $product->getFirstMedia('logo', 'thumb') : $product->getFirstMedia('logo');

        if ($thumb && !$media) {
            $media = $product->getFirstMedia('logo');
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

    public function delete(Product $product)
    {
        $media = $product->getFirstMedia('logo');
        $product->clearMediaCollection('logo');

        if ($media) {
            $product->logModelActivity(ProductActivityType::LogoDeleted->value, $media);
        }

        return response()->json(['message' => 'Product logo deleted.'], HttpResponse::HTTP_NO_CONTENT);
    }
}
