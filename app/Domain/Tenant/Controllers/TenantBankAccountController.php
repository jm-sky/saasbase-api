<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Common\Resources\BankAccountResource;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\StoreTenantBankAccountRequest;
use App\Domain\Tenant\Requests\UpdateTenantBankAccountRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TenantBankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Tenant $tenant): AnonymousResourceCollection
    {
        return BankAccountResource::collection($tenant->bankAccounts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTenantBankAccountRequest $request, Tenant $tenant): BankAccountResource
    {
        $bankAccount = $tenant->bankAccounts()->create($request->validated());

        return new BankAccountResource($bankAccount);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant, int $bankAccountId): BankAccountResource
    {
        $bankAccount = $tenant->bankAccounts()->findOrFail($bankAccountId);

        return new BankAccountResource($bankAccount);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTenantBankAccountRequest $request, Tenant $tenant, int $bankAccountId): BankAccountResource
    {
        $bankAccount = $tenant->bankAccounts()->findOrFail($bankAccountId);
        $bankAccount->update($request->validated());

        return new BankAccountResource($bankAccount);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant, int $bankAccountId): Response
    {
        $bankAccount = $tenant->bankAccounts()->findOrFail($bankAccountId);
        $bankAccount->delete();

        return response()->noContent();
    }
}
