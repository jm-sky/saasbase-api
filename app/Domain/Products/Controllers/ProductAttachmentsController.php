<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Products\Enums\ProductActivityType;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Requests\ProductAttachmentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ProductAttachmentsController extends Controller
{
    /**
     * List all attachments for a product.
     */
    public function index(Product $product)
    {
        $attachments = $product->getMedia('attachments');

        return response()->json([
            'data' => $attachments->map(fn ($media) => [
                'id'        => $media->id,
                'name'      => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size'      => $media->size,
                'url'       => route('product.attachments.show', ['product' => $product->id, 'attachment' => $media->id], absolute: false),
            ]),
        ]);
    }

    /**
     * Upload a new attachment.
     */
    public function store(ProductAttachmentRequest $request, Product $product)
    {
        $media = $product->addMediaFromRequest('file')
            ->toMediaCollection('attachments')
        ;

        activity()
            ->performedOn($product)
            ->withProperties([
                'tenant_id'     => request()->user()?->getTenantId(),
                'product_id'    => $product->id,
                'attachment_id' => $media->id,
            ])
            ->event(ProductActivityType::AttachmentCreated->value)
            ->log('Product attachment created')
        ;

        return response()->json([
            'message' => 'Attachment uploaded successfully.',
            'data'    => [
                'id'        => $media->id,
                'name'      => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size'      => $media->size,
                'url'       => route('product.attachments.show', ['product' => $product->id, 'attachment' => $media->id], absolute: false),
            ],
        ], HttpResponse::HTTP_CREATED);
    }

    /**
     * Show metadata for a single attachment.
     */
    public function show(Product $product, Request $request, $attachmentId)
    {
        $media = $product->getMedia('attachments')->firstWhere('id', $attachmentId);

        if (!$media) {
            return response()->json(['message' => 'Attachment not found.'], HttpResponse::HTTP_NOT_FOUND);
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

    /**
     * Update an attachment.
     */
    public function update(ProductAttachmentRequest $request, Product $product, $attachmentId)
    {
        $media = $product->getMedia('attachments')->firstWhere('id', $attachmentId);

        if (!$media) {
            return response()->json(['message' => 'Attachment not found.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $media->delete();
        $newMedia = $product->addMediaFromRequest('file')
            ->toMediaCollection('attachments')
        ;

        activity()
            ->performedOn($product)
            ->withProperties([
                'tenant_id'     => request()->user()?->getTenantId(),
                'product_id'    => $product->id,
                'attachment_id' => $newMedia->id,
            ])
            ->event(ProductActivityType::AttachmentUpdated->value)
            ->log('Product attachment updated')
        ;

        return response()->json([
            'message' => 'Attachment updated successfully.',
            'data'    => [
                'id'        => $newMedia->id,
                'name'      => $newMedia->name,
                'file_name' => $newMedia->file_name,
                'mime_type' => $newMedia->mime_type,
                'size'      => $newMedia->size,
                'url'       => route('product.attachments.show', ['product' => $product->id, 'attachment' => $newMedia->id], absolute: false),
            ],
        ]);
    }

    /**
     * Delete an attachment.
     */
    public function destroy(Product $product, $attachmentId)
    {
        $media = $product->getMedia('attachments')->firstWhere('id', $attachmentId);

        if (!$media) {
            return response()->json(['message' => 'Attachment not found.'], HttpResponse::HTTP_NOT_FOUND);
        }

        $media->delete();

        activity()
            ->performedOn($product)
            ->withProperties([
                'tenant_id'     => request()->user()?->getTenantId(),
                'product_id'    => $product->id,
                'attachment_id' => $media->id,
            ])
            ->event(ProductActivityType::AttachmentDeleted->value)
            ->log('Product attachment deleted')
        ;

        return response()->json(null, HttpResponse::HTTP_NO_CONTENT);
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
