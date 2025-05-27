<?php

namespace App\Domain\Utils\Controllers;

use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Utils\Requests\CompanyLookupRequest;
use App\Domain\Utils\Resources\CommonCompanyLookupResource;
use App\Http\Controllers\Controller;
use App\Services\CompanyLookup\Services\CompanyLookupService;
use App\Services\GusLookup\Services\GusLookupService;
use App\Services\ViesLookup\Services\ViesLookupService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CompanyLookupController extends Controller
{
    public const IS_PL_COUNTRY = 'PL';

    public function __construct(
        private readonly CompanyLookupService $companyLookupService,
        private readonly ViesLookupService $viesLookupService,
        private readonly GusLookupService $gusLookupService,
    ) {
    }

    public function lookup(CompanyLookupRequest $request): CommonCompanyLookupResource
    {
        $vatId   = $request->input('vatId');
        $country = strtoupper($request->input('country'));
        $force   = $request->user()?->isAdmin() ? $request->boolean('force', false) : false;

        try {
            $result = $this->find($vatId, $country, $force);

            return new CommonCompanyLookupResource($result);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }

    protected function find(string $vatId, string $country, bool $force = false): ?CommonCompanyLookupData
    {
        if (self::IS_PL_COUNTRY === $country) {
            return $this->findByNip($vatId, $force);
        }

        $result = $this->viesLookupService->findByVat($country, $vatId);

        return $result ? $result->toCommonLookupData() : null;
    }

    protected function findByNip(string $nip, bool $force = false): ?CommonCompanyLookupData
    {
        $gusData = null;

        if (config('gus_lookup.user_key')) {
            $gusData = $this->gusLookupService->findByNip($nip, $force);
        }

        $mfData = $this->companyLookupService->findByNip($nip, $force);

        if (!$gusData && !$mfData) {
            return null;
        }

        if (!$mfData && $gusData) {
            return $gusData->toCommonLookupData();
        }

        $result = $mfData->toCommonLookupData();

        if ($gusData) {
            $gusResult = $gusData->toCommonLookupData();
            // Merge GUS data with MF data, preferring GUS data for overlapping fields
            $result = new CommonCompanyLookupData(
                name: $gusResult->name,
                country: $result->country,
                vatId: $result->vatId,
                regon: $gusResult->regon,
                shortName: $gusResult->shortName,
                phoneNumber: $gusResult->phoneNumber,
                email: $gusResult->email,
                website: $gusResult->website,
                address: $gusResult->address,
                bankAccount: $result->bankAccount
            );
        }

        return $result;
    }
}
