<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Common\Models\BankAccount;
use App\Domain\Common\Resources\BankAccountResource;
use App\Domain\Tenant\Enums\TenantActivityType;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\StoreTenantBankAccountRequest;
use App\Domain\Tenant\Requests\UpdateTenantBankAccountRequest;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TenantBankAccountController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Tenant $tenant): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [BankAccount::class, $tenant]);

        return BankAccountResource::collection($tenant->bankAccounts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTenantBankAccountRequest $request, Tenant $tenant): BankAccountResource
    {
        $this->authorize('create', [BankAccount::class, $tenant]);

        $bankAccount = $tenant->bankAccounts()->create($request->validated());

        return new BankAccountResource($bankAccount);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant, string $bankAccountId): BankAccountResource
    {
        $bankAccount = $tenant->bankAccounts()->findOrFail($bankAccountId);
        $this->authorize('view', [$bankAccount, $tenant]);

        return new BankAccountResource($bankAccount);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTenantBankAccountRequest $request, Tenant $tenant, string $bankAccountId): BankAccountResource
    {
        $bankAccount = $tenant->bankAccounts()->findOrFail($bankAccountId);
        $this->authorize('update', [$bankAccount, $tenant]);

        $bankAccount->update($request->validated());

        return new BankAccountResource($bankAccount);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant, string $bankAccountId): Response
    {
        $bankAccount = $tenant->bankAccounts()->findOrFail($bankAccountId);
        $this->authorize('delete', [$bankAccount, $tenant]);

        $bankAccount->delete();

        return response()->noContent();
    }

    public function setDefault(Tenant $tenant, BankAccount $bankAccount): JsonResponse
    {
        abort_if($bankAccount->bankable_id !== $tenant->id, Response::HTTP_NOT_FOUND);

        $tenant->bankAccounts()->update(['is_default' => false]);
        $bankAccount->update(['is_default' => true]);
        $tenant->logModelActivity(TenantActivityType::BankAccountSetDefault->value, $bankAccount);

        return response()->json(['message' => 'Bank account set as default successfully.'], Response::HTTP_NO_CONTENT);
    }
}
