<?php

namespace App\Domain\Financial\Controllers;

use App\Domain\Financial\Models\PKWiUClassification;
use App\Domain\Financial\Resources\PKWiUClassificationResource;
use App\Domain\Financial\Services\PKWiUService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PKWiUClassificationController extends Controller
{
    public function __construct(
        private PKWiUService $pkwiuService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = PKWiUClassification::query()->active();

        if ($request->has('search')) {
            $results = $this->pkwiuService->searchByName($request->search, $request->per_page ?? 50);
        } else {
            if ($request->has('level')) {
                $query->byLevel($request->level);
            }

            if ($request->has('parent_code')) {
                $query->where('parent_code', $request->parent_code);
            }

            $results = $query->paginate($request->per_page ?? 50);
        }

        return response()->json([
            'data' => PKWiUClassificationResource::collection($results),
            'meta' => $results instanceof \Illuminate\Pagination\LengthAwarePaginator ? [
                'total'        => $results->total(),
                'per_page'     => $results->perPage(),
                'current_page' => $results->currentPage(),
            ] : null,
        ]);
    }

    public function show(string $code): JsonResponse
    {
        $classification = PKWiUClassification::with('children', 'parent')->find($code);

        if (!$classification) {
            return response()->json(['message' => 'Classification not found'], 404);
        }

        return response()->json([
            'data' => new PKWiUClassificationResource($classification),
        ]);
    }

    public function tree(Request $request): JsonResponse
    {
        $tree = $this->pkwiuService->getHierarchyTree($request->parent_code);

        return response()->json([
            'data' => PKWiUClassificationResource::collection($tree),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:255',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        $results = $this->pkwiuService->searchByName(
            $request->query,
            $request->limit ?? 50
        );

        return response()->json([
            'data' => PKWiUClassificationResource::collection($results),
        ]);
    }

    public function validateCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|regex:/^[0-9]{2}\.[0-9]{2}\.[0-9]{2}\.[0-9]$/',
        ]);

        $isValid = $this->pkwiuService->validateCode($request->code);

        return response()->json([
            'valid'   => $isValid,
            'code'    => $request->code,
            'message' => $isValid ? 'Valid PKWiU code' : 'Invalid PKWiU code',
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $request->validate([
            'partial' => 'required|string|min:1|max:50',
        ]);

        $suggestions = $this->pkwiuService->getCodeSuggestions($request->partial);

        return response()->json([
            'data' => PKWiUClassificationResource::collection($suggestions),
        ]);
    }

    public function validateInvoiceBody(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_body'              => 'required|array',
            'invoice_body.*.pkwiu_code' => 'required|string|regex:/^[0-9]{2}\.[0-9]{2}\.[0-9]{2}\.[0-9]$/',
        ]);

        $errors = $this->pkwiuService->validateInvoiceBodyPKWiU($request->invoice_body);

        return response()->json([
            'valid'  => empty($errors),
            'errors' => $errors,
        ]);
    }
}
