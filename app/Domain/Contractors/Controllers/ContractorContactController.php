<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Contractors\DTOs\ContractorContactPersonDTO;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Models\ContractorContactPerson;
use App\Domain\Contractors\Requests\ContractorContactPersonRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ContractorContactController extends Controller
{
    public function index(Contractor $contractor): JsonResponse
    {
        $contacts = $contractor->contacts()->paginate();

        return response()->json([
            'data' => collect($contacts->items())->map(fn (ContractorContactPerson $contact) => ContractorContactPersonDTO::fromModel($contact)),
            'meta' => [
                'current_page' => $contacts->currentPage(),
                'last_page'    => $contacts->lastPage(),
                'per_page'     => $contacts->perPage(),
                'total'        => $contacts->total(),
            ],
        ]);
    }

    public function store(ContractorContactPersonRequest $request, Contractor $contractor): JsonResponse
    {
        $contact = $contractor->contacts()->create($request->validated());

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'     => request()->user()?->getTenantId(),
                'contractor_id' => $contractor->id,
                'contact_id'    => $contact->id,
            ])
            ->event(ContractorActivityType::ContactCreated->value)
            ->log('Contractor contact created')
        ;

        return response()->json([
            'message' => 'Contact created successfully.',
            'data'    => ContractorContactPersonDTO::fromModel($contact),
        ], Response::HTTP_CREATED);
    }

    public function show(Contractor $contractor, ContractorContactPerson $contact): JsonResponse
    {
        abort_if($contact->contractor_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        return response()->json([
            'data' => ContractorContactPersonDTO::fromModel($contact),
        ]);
    }

    public function update(ContractorContactPersonRequest $request, Contractor $contractor, ContractorContactPerson $contact): JsonResponse
    {
        abort_if($contact->contractor_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        $contact->update($request->validated());

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'     => request()->user()?->getTenantId(),
                'contractor_id' => $contractor->id,
                'contact_id'    => $contact->id,
            ])
            ->event(ContractorActivityType::ContactUpdated->value)
            ->log('Contractor contact updated')
        ;

        return response()->json([
            'message' => 'Contact updated successfully.',
            'data'    => ContractorContactPersonDTO::fromModel($contact->fresh()),
        ]);
    }

    public function destroy(Contractor $contractor, ContractorContactPerson $contact): JsonResponse
    {
        abort_if($contact->contractor_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'     => request()->user()?->getTenantId(),
                'contractor_id' => $contractor->id,
                'contact_id'    => $contact->id,
            ])
            ->event(ContractorActivityType::ContactDeleted->value)
            ->log('Contractor contact deleted')
        ;

        $contact->delete();

        return response()->json(['message' => 'Contact deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
