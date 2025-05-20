<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\Models\BankAccount;
use App\Domain\Common\Resources\BankAccountResource;
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

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'       => request()->user()?->getTenantId(),
                'contractor_id'   => $contractor->id,
                'bank_account_id' => $bankAccount->id,
            ])
            ->event(ContractorActivityType::BankAccountCreated->value)
            ->log('Contractor bank account created')
        ;

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
    public function update(UpdateContractorBankAccountRequest $request, Contractor $contractor, int $bankAccountId): BankAccountResource
    {
        $bankAccount = $contractor->bankAccounts()->findOrFail($bankAccountId);
        $bankAccount->update($request->validated());

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'       => request()->user()?->getTenantId(),
                'contractor_id'   => $contractor->id,
                'bank_account_id' => $bankAccount->id,
            ])
            ->event(ContractorActivityType::BankAccountUpdated->value)
            ->log('Contractor bank account updated')
        ;

        return new BankAccountResource($bankAccount);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contractor $contractor, int $bankAccountId): Response
    {
        $bankAccount = $contractor->bankAccounts()->findOrFail($bankAccountId);
        $bankAccount->delete();

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'       => request()->user()?->getTenantId(),
                'contractor_id'   => $contractor->id,
                'bank_account_id' => $bankAccount->id,
            ])
            ->event(ContractorActivityType::BankAccountDeleted->value)
            ->log('Contractor bank account deleted')
        ;

        return response()->noContent();
    }

    public function setDefault(Contractor $contractor, BankAccount $bankAccount): JsonResponse
    {
        abort_if($bankAccount->bankable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        $contractor->bankAccounts()->update(['is_default' => false]);
        $bankAccount->update(['is_default' => true]);

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'       => request()->user()?->getTenantId(),
                'contractor_id'   => $contractor->id,
                'bank_account_id' => $bankAccount->id,
            ])
            ->event(ContractorActivityType::BankAccountSetDefault->value)
            ->log('Contractor bank account set as default')
        ;

        return response()->json(['message' => 'Bank account set as default successfully.'], Response::HTTP_NO_CONTENT);
    }
}
