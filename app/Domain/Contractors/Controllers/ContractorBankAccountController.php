<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\Models\BankAccount;
use App\Domain\Common\Resources\BankAccountResource;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Requests\StoreContractorBankAccountRequest;
use App\Domain\Contractors\Requests\UpdateContractorBankAccountRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ContractorBankAccountController extends Controller
{
    use HasActivityLogging;

    /**
     * Display a listing of the resource.
     */
    public function index(Contractor $contractor): AnonymousResourceCollection
    {
        return BankAccountResource::collection($contractor->bankAccounts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContractorBankAccountRequest $request, Contractor $contractor): BankAccountResource
    {
        $bankAccount = $contractor->bankAccounts()->create($request->validated());
        $contractor->logModelActivity(ContractorActivityType::BankAccountCreated->value, $bankAccount);

        return new BankAccountResource($bankAccount);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contractor $contractor, int $bankAccountId): BankAccountResource
    {
        $bankAccount = $contractor->bankAccounts()->findOrFail($bankAccountId);

        return new BankAccountResource($bankAccount);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContractorBankAccountRequest $request, Contractor $contractor, string $bankAccountId): BankAccountResource
    {
        $bankAccount = $contractor->bankAccounts()->findOrFail($bankAccountId);
        $bankAccount->update($request->validated());
        $contractor->logModelActivity(ContractorActivityType::BankAccountUpdated->value, $bankAccount);

        return new BankAccountResource($bankAccount);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contractor $contractor, string $bankAccountId): Response
    {
        $bankAccount = $contractor->bankAccounts()->findOrFail($bankAccountId);
        $contractor->logModelActivity(ContractorActivityType::BankAccountDeleted->value, $bankAccount);
        $bankAccount->delete();

        return response()->noContent();
    }

    public function setDefault(Contractor $contractor, BankAccount $bankAccount): JsonResponse
    {
        abort_if($bankAccount->bankable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        $contractor->bankAccounts()->update(['is_default' => false]);
        $bankAccount->update(['is_default' => true]);
        $contractor->logModelActivity(ContractorActivityType::BankAccountSetDefault->value, $bankAccount);

        return response()->json(['message' => 'Bank account set as default successfully.'], Response::HTTP_NO_CONTENT);
    }
}
