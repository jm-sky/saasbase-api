<?php

namespace App\Domain\Utils\Controllers;

use App\Domain\Utils\Requests\GetIbanInfoRequest;
use App\Domain\Utils\Resources\IbanInfoResource;
use App\Http\Controllers\Controller;
use App\Services\IbanInfo\IbanInfoService;
use Illuminate\Http\Resources\Json\JsonResource;

class IbanInfoController extends Controller
{
    public function __construct(
        private readonly IbanInfoService $ibanInfoService
    ) {
    }

    public function __invoke(GetIbanInfoRequest $request): IbanInfoResource
    {
        $ibanInfo = $this->ibanInfoService->getBankInfoFromIban(
            $request->iban,
            $request->input('country')
        );

        if (!$ibanInfo) {
            return new JsonResource(['error' => 'Bank not found for the provided IBAN']);
        }

        return new IbanInfoResource($ibanInfo->toArray());
    }
}
