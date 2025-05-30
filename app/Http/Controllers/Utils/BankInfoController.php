<?php

namespace App\Http\Controllers\Utils;

use App\Domain\Bank\Requests\GetBankInfoRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\BankInfoResource;
use App\Services\IbanInfo\IbanInfoService;
use Illuminate\Http\Resources\Json\JsonResource;

class BankInfoController extends Controller
{
    public function __construct(
        private readonly IbanInfoService $ibanInfoService
    ) {
    }

    public function __invoke(GetBankInfoRequest $request): JsonResource
    {
        $bankInfo = $this->ibanInfoService->getBankInfoFromIban(
            $request->iban,
            $request->input('country')
        );

        if (!$bankInfo) {
            return new JsonResource(['error' => 'Bank not found for the provided IBAN']);
        }

        return new BankInfoResource($bankInfo->toArray());
    }
}
