<?php

namespace App\Http\Controllers\Utils;

use App\Domain\Bank\Requests\GetBankInfoRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\BankInfoResource;
use App\Services\BankRoutingService;
use Illuminate\Http\Resources\Json\JsonResource;

class BankInfoController extends Controller
{
    public function __construct(
        private readonly BankRoutingService $bankRoutingService
    ) {
    }

    public function __invoke(GetBankInfoRequest $request): JsonResource
    {
        $bankInfo = $this->bankRoutingService->getBankInfoFromIban(
            $request->iban,
            $request->input('country')
        );

        if (!$bankInfo) {
            return new JsonResource(['error' => 'Bank not found for the provided IBAN']);
        }

        return new BankInfoResource($bankInfo);
    }
}
