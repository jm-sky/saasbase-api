<?php

namespace App\Domain\Common\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\Media;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Products\Models\Product;
use App\Domain\Tenant\Models\Tenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SignedFileController extends Controller
{
    public const MAX_AGE = 900; // 15 minutes

    public function show(Request $request, string $modelName, string $modelId, string $mediaId, string $fileName)
    {
        $modelClass = $this->resolveModelClass($modelName);

        $media = Media::where('id', $mediaId)
            ->where('model_type', $modelClass)
            ->where('model_id', $modelId)
            ->where('file_name', $fileName)
            ->firstOrFail()
        ;

        $stream = Storage::disk($media->disk)->readStream($media->getPath());

        return Response::stream(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        }, HttpResponse::HTTP_OK, [
            'Content-Type'        => $media->mime_type,
            'Content-Length'      => $media->size,
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
            'Cache-Control'       => 'private, max-age=' . self::MAX_AGE,
        ]);
    }

    protected function resolveModelClass(string $modelName): string
    {
        return match ($modelName) {
            'users'       => User::class,
            'contractors' => Contractor::class,
            'products'    => Product::class,
            'tenants'     => Tenant::class,
            default       => throw new NotFoundHttpException('Model not found'),
        };
    }
}
