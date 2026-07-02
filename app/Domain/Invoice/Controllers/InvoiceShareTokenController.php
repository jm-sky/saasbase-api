<?php

namespace App\Domain\Invoice\Controllers;

use App\Domain\Invoice\Models\Invoice;
use App\Domain\Invoice\Resources\StoreInvoiceShareTokenRequest;
use App\Domain\ShareToken\Models\ShareToken;
use App\Domain\ShareToken\Resources\ShareTokenResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class InvoiceShareTokenController extends Controller
{
    public function index(Invoice $invoice): AnonymousResourceCollection
    {
        $this->authorize('view', $invoice);

        return ShareTokenResource::collection($invoice->shareTokens);
    }

    public function store(Invoice $invoice, StoreInvoiceShareTokenRequest $request): ShareTokenResource
    {
        $this->authorize('update', $invoice);

        $shareToken = $invoice->shareTokens()->create($request->validated());

        return new ShareTokenResource($shareToken);
    }

    /**
     * Revoke a share token. This previously deleted the whole Invoice —
     * the {share_token} route parameter was silently ignored because the
     * method signature only accepted $invoice, so every request hit
     * Invoice::delete() instead of revoking the link.
     */
    public function destroy(Invoice $invoice, ShareToken $shareToken): JsonResponse
    {
        $this->authorize('update', $invoice);

        abort_if(
            $shareToken->shareable_type !== Invoice::class || $shareToken->shareable_id !== $invoice->id,
            HttpResponse::HTTP_NOT_FOUND
        );

        $shareToken->delete();

        return response()->json(['message' => 'Share token revoked successfully.'], Response::HTTP_NO_CONTENT);
    }
}
