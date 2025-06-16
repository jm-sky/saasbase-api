<?php

namespace App\Domain\Invoice\Controllers;

use App\Domain\Invoice\Models\Invoice;
use App\Domain\Invoice\Resources\StoreInvoiceShareTokenRequest;
use App\Domain\ShareToken\Resources\ShareTokenResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class InvoiceShareTokenController extends Controller
{
    public function index(Invoice $invoice): AnonymousResourceCollection
    {
        return ShareTokenResource::collection($invoice->shareTokens);
    }

    public function store(Invoice $invoice, StoreInvoiceShareTokenRequest $request): ShareTokenResource
    {
        $shareToken = $invoice->shareTokens()->create($request->validated());

        return new ShareTokenResource($shareToken);
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
