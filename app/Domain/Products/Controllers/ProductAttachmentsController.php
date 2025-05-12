<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Common\DTOs\MediaDTO;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Requests\ProductAttachmentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductAttachmentsController extends Controller
{
    /**
     * List all attachments for a product.
     */
    public function index(Product $product)
    {
        $media = $product->getMedia('attachments');

        return response()->json([
            'data' => MediaDTO::collection($media),
        ]);
    }

    /**
     * Upload a new attachment.
     */
    public function store(ProductAttachmentRequest $request, Product $product)
    {
        $file  = $request->file('file');
        $media = $product->addMedia($file)->toMediaCollection('attachments');

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show metadata for a single attachment.
     */
    public function show(Product $product, Media $media)
    {
        $this->authorizeMedia($product, $media);

        return response()->json([
            'data' => MediaDTO::fromModel($media)->toArray(),
        ]);
    }

    /**
     * Download an attachment.
     */
    public function download(Product $product, Media $media)
    {
        $this->authorizeMedia($product, $media);
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
    public function preview(Product $product, Media $media)
    {
        $this->authorizeMedia($product, $media);
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
    public function destroy(Product $product, Media $media)
    {
        $this->authorizeMedia($product, $media);
        $media->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Ensure the media belongs to the product.
     */
    protected function authorizeMedia(Product $product, Media $media): void
    {
        if (Product::class !== $media->model_type || $media->model_id !== $product->id) {
            abort(Response::HTTP_NOT_FOUND, 'Attachment not found for this product.');
        }
    }
}
