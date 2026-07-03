<?php

namespace App\Domain\Invoice\Controllers;

use App\Domain\Invoice\Models\Invoice;
use App\Domain\Invoice\Resources\StoreInvoiceShareTokenRequest;
use App\Domain\ShareToken\Models\ShareToken;
use App\Domain\ShareToken\Resources\ShareTokenResource;
use App\Domain\ShareToken\Services\ShareTokenService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
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

    /**
     * The request only validates expiresAt/onlyForAuthenticated/maxUsage —
     * it never carried the token itself or shareable_type (both NOT NULL
     * columns), so $invoice->shareTokens()->create($request->validated())
     * always threw a DB exception. ShareTokenService::createToken() (which
     * generates the actual crypto-random token) already existed but was
     * never called from anywhere. Wiring it in here.
     */
    public function store(Invoice $invoice, StoreInvoiceShareTokenRequest $request, ShareTokenService $shareTokenService): ShareTokenResource
    {
        $this->authorize('update', $invoice);

        $data = $request->validated();

        $shareToken = $shareTokenService->createToken(
            shareableType: Invoice::class,
            shareableId: $invoice->id,
            onlyForAuthenticated: (bool) $data['only_for_authenticated'],
            expiresAt: Carbon::parse($data['expires_at']),
            maxUsage: $data['max_usage'],
        );

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
            Invoice::class !== $shareToken->shareable_type || $shareToken->shareable_id !== $invoice->id,
            HttpResponse::HTTP_NOT_FOUND
        );

        $shareToken->delete();

        return response()->json(['message' => 'Share token revoked successfully.'], Response::HTTP_NO_CONTENT);
    }
}
