<?php

namespace App\Http\Controllers\Domain\Utils\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\Utils\Requests\CompanyLookupRequest;
use App\Services\CompanyLookup\Services\CompanyLookupService;
use App\Services\ViesLookup\Services\ViesLookupService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CompanyLookupController extends Controller
{
    public function __construct(
        private readonly CompanyLookupService $companyLookupService,
        private readonly ViesLookupService $viesLookupService,
    ) {
    }

    public function lookup(CompanyLookupRequest $request): JsonResponse
    {
        $vatId   = $request->input('vatId');
        $country = strtoupper($request->input('country'));
        $force   = $request->user()?->isAdmin() ? $request->boolean('force', false) : false;

        try {
            if ('PL' === $country) {
                $result = $this->companyLookupService->findByNip($vatId, $force);
            } else {
                $result = $this->viesLookupService->findByVat($country, $vatId);
            }

            if (!$result) {
                return response()->json([
                    'message' => 'Company not found',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
