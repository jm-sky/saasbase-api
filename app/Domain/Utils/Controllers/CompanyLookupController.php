<?php

namespace App\Domain\Utils\Controllers;

use App\Domain\Utils\Requests\CompanyLookupRequest;
use App\Domain\Utils\Resources\CompanyLookupResource;
use App\Http\Controllers\Controller;
use App\Services\CompanyLookup\Services\CompanyLookupService;
use App\Services\ViesLookup\Services\ViesLookupService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanyLookupController extends Controller
{
    public function __construct(
        private readonly CompanyLookupService $companyLookupService,
        private readonly ViesLookupService $viesLookupService,
    ) {
    }

    public function lookup(CompanyLookupRequest $request): CompanyLookupResource
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
                throw new NotFoundHttpException('Company not found');
            }

            return new CompanyLookupResource($result);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }
}
