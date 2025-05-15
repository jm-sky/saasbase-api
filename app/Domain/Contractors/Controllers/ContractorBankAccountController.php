<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\DTOs\BankAccountDTO;
use App\Domain\Common\Models\BankAccount;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Requests\ContractorBankAccountRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ContractorBankAccountController extends Controller
{
    public function index(Contractor $contractor): JsonResponse
    {
        $bankAccounts = $contractor->bankAccounts()->orderBy('is_default', 'desc')->paginate();

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

    public function store(ContractorBankAccountRequest $request, Contractor $contractor): JsonResponse
    {
        $bankAccount = $contractor->bankAccounts()->create($request->validated());

        return response()->json([
            'data' => BankAccountDTO::fromModel($bankAccount),
        ], Response::HTTP_CREATED);
    }

    public function show(Contractor $contractor, BankAccount $bankAccount): JsonResponse
    {
        abort_if($bankAccount->bankable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        return response()->json([
            'data' => BankAccountDTO::fromModel($bankAccount),
        ]);
    }

    public function update(ContractorBankAccountRequest $request, Contractor $contractor, BankAccount $bankAccount): JsonResponse
    {
        abort_if($bankAccount->bankable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        $bankAccount->update($request->validated());

        return response()->json([
            'data' => BankAccountDTO::fromModel($bankAccount->fresh()),
        ]);
    }

    public function destroy(Contractor $contractor, BankAccount $bankAccount): JsonResponse
    {
        abort_if($bankAccount->bankable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        $bankAccount->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function setDefault(Contractor $contractor, BankAccount $bankAccount): JsonResponse
    {
        $contractor->bankAccounts()->update(['is_default' => false]);
        $bankAccount->update(['is_default' => true]);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
