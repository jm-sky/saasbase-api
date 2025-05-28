<?php

namespace App\Domain\Utils\Controllers;

use App\Domain\Utils\Requests\CompanyLookupRequest;
use App\Domain\Utils\Resources\CommonCompanyLookupResource;
use App\Domain\Utils\Services\CompanyDataAutoFillService;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CompanyLookupController extends Controller
{
    public function __construct(
        private readonly CompanyDataAutoFillService $autoFillService,
    ) {
    }

    public function lookup(CompanyLookupRequest $request): CommonCompanyLookupResource
    {
        $vatId   = $request->input('vatId');
        $regon   = $request->input('regon');
        $country = strtoupper($request->input('country'));
        $force   = $request->user()?->isAdmin() ? $request->boolean('force', false) : false;

        try {
            $result = $this->autoFillService->autoFill($vatId, $regon, $country, $force);

            return new CommonCompanyLookupResource($result);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }
}
