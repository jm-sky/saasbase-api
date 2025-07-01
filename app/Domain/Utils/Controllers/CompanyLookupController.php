<?php

namespace App\Domain\Utils\Controllers;

use App\Domain\Utils\Requests\CompanyLookupRequest;
use App\Domain\Utils\Resources\CommonCompanyLookupResource;
use App\Domain\Utils\Services\CompanyDataAutoFillService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CompanyLookupController extends Controller
{
    public function __construct(
        private readonly CompanyDataAutoFillService $autoFillService,
    ) {
    }

    public function lookup(CompanyLookupRequest $request): CommonCompanyLookupResource|JsonResponse
    {
        $vatId   = $request->input('vatId');
        $regon   = $request->input('regon');
        $country = strtoupper($request->input('country'));
        $force   = $request->user()?->isAdmin() ? $request->boolean('force', false) : false;

        try {
            $result = $this->autoFillService->autoFill($vatId, $regon, $country, $force);

            return $result ? new CommonCompanyLookupResource($result) : new JsonResponse([], Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }
}
