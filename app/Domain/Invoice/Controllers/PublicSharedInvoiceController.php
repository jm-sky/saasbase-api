<?php

namespace App\Domain\Invoice\Controllers;

use App\Domain\Invoice\Models\Invoice;
use App\Domain\ShareToken\Models\ShareToken;
use App\Domain\ShareToken\Services\ShareTokenService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PublicSharedInvoiceController extends Controller
{
    public function show(string $token, ShareTokenService $shareTokenService): JsonResponse
    {
        $shareToken = ShareToken::query()
            ->where('token', $token)
            ->where('shareable_type', Invoice::class)
            ->first();

        abort_if(! $shareToken, Response::HTTP_NOT_FOUND);
        abort_unless($shareTokenService->validateToken($shareToken), Response::HTTP_GONE);
        abort_if($shareToken->only_for_authenticated && ! auth('api')->check(), Response::HTTP_UNAUTHORIZED);

        /** @var Invoice $invoice */
        $invoice = Invoice::query()->findOrFail($shareToken->shareable_id);

        $shareTokenService->incrementUsage($shareToken);

        return response()->json([
            'data' => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'type' => $invoice->type->value,
                'status' => $invoice->status->value,
                'issueDate' => $invoice->issue_date?->toDateString(),
                'currency' => $invoice->currency,
                'totalNet' => $invoice->total_net->toFloat(),
                'totalTax' => $invoice->total_tax->toFloat(),
                'totalGross' => $invoice->total_gross->toFloat(),
                'seller' => $invoice->seller?->toArray(),
                'buyer' => $invoice->buyer?->toArray(),
            ],
        ]);
    }
}
