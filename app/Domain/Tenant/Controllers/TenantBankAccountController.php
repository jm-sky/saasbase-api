<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Common\DTOs\BankAccountDTO;
use App\Domain\Common\Models\BankAccount;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\TenantBankAccountRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TenantBankAccountController extends Controller
{
    public function index(Tenant $tenant): JsonResponse
    {
        $bankAccounts = $tenant->bankAccounts()->orderBy('is_default', 'desc')->paginate();

        return response()->json([
            'data' => collect($bankAccounts->items())->map(fn (BankAccount $bankAccount) => BankAccountDTO::fromModel($bankAccount)),
            'meta' => [
                'current_page' => $bankAccounts->currentPage(),
                'last_page'    => $bankAccounts->lastPage(),
                'per_page'     => $bankAccounts->perPage(),
                'total'        => $bankAccounts->total(),
            ],
        ]);
    }

    public function store(TenantBankAccountRequest $request, Tenant $tenant): JsonResponse
    {
        $bankAccount = $tenant->bankAccounts()->create($request->validated());

        return response()->json([
            'data' => BankAccountDTO::fromModel($bankAccount),
        ], Response::HTTP_CREATED);
    }

    public function show(Tenant $tenant, BankAccount $bankAccount): JsonResponse
    {
        abort_if($bankAccount->bankable_id !== $tenant->id, Response::HTTP_NOT_FOUND);

        return response()->json([
            'data' => BankAccountDTO::fromModel($bankAccount),
        ]);
    }

    public function update(TenantBankAccountRequest $request, Tenant $tenant, BankAccount $bankAccount): JsonResponse
    {
        abort_if($bankAccount->bankable_id !== $tenant->id, Response::HTTP_NOT_FOUND);

        $bankAccount->update($request->validated());

        return response()->json([
            'data' => BankAccountDTO::fromModel($bankAccount->fresh()),
        ]);
    }

    public function destroy(Tenant $tenant, BankAccount $bankAccount): JsonResponse
    {
        abort_if($bankAccount->bankable_id !== $tenant->id, Response::HTTP_NOT_FOUND);

        $bankAccount->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function setDefault(Tenant $tenant, BankAccount $bankAccount): JsonResponse
    {
        $tenant->bankAccounts()->update(['is_default' => false]);
        $bankAccount->update(['is_default' => true]);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
