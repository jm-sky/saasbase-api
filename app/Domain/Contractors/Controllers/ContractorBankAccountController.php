<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\DTOs\BankAccountDTO;
use App\Domain\Common\Models\BankAccount;
use App\Domain\Contractors\Enums\ContractorActivityType;
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

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'       => request()->user()?->tenant_id,
                'contractor_id'   => $contractor->id,
                'bank_account_id' => $bankAccount->id,
            ])
            ->event(ContractorActivityType::BankAccountCreated->value)
            ->log('Contractor bank account created')
        ;

        return response()->json([
            'message' => 'Bank account created successfully.',
            'data'    => BankAccountDTO::fromModel($bankAccount),
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

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'       => request()->user()?->tenant_id,
                'contractor_id'   => $contractor->id,
                'bank_account_id' => $bankAccount->id,
            ])
            ->event(ContractorActivityType::BankAccountUpdated->value)
            ->log('Contractor bank account updated')
        ;

        return response()->json([
            'message' => 'Bank account updated successfully.',
            'data'    => BankAccountDTO::fromModel($bankAccount->fresh()),
        ]);
    }

    public function destroy(Contractor $contractor, BankAccount $bankAccount): JsonResponse
    {
        abort_if($bankAccount->bankable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'       => request()->user()?->tenant_id,
                'contractor_id'   => $contractor->id,
                'bank_account_id' => $bankAccount->id,
            ])
            ->event(ContractorActivityType::BankAccountDeleted->value)
            ->log('Contractor bank account deleted')
        ;

        $bankAccount->delete();

        return response()->json(['message' => 'Bank account deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

    public function setDefault(Contractor $contractor, BankAccount $bankAccount): JsonResponse
    {
        abort_if($bankAccount->bankable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        $contractor->bankAccounts()->update(['is_default' => false]);
        $bankAccount->update(['is_default' => true]);

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'       => request()->user()?->tenant_id,
                'contractor_id'   => $contractor->id,
                'bank_account_id' => $bankAccount->id,
            ])
            ->event(ContractorActivityType::BankAccountSetDefault->value)
            ->log('Contractor bank account set as default')
        ;

        return response()->json(['message' => 'Bank account set as default successfully.'], Response::HTTP_NO_CONTENT);
    }
}
