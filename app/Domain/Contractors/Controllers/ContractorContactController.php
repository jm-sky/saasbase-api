<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\Traits\HasActivityLogging;
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
    use HasActivityLogging;

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
        $contractor->logModelActivity(ContractorActivityType::ContactCreated->value, $contact);

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
        $contractor->logModelActivity(ContractorActivityType::ContactUpdated->value, $contact);

        return response()->json([
            'message' => 'Contact updated successfully.',
            'data'    => ContractorContactPersonDTO::fromModel($contact->fresh()),
        ]);
    }

    public function destroy(Contractor $contractor, ContractorContactPerson $contact): JsonResponse
    {
        abort_if($contact->contractor_id !== $contractor->id, Response::HTTP_NOT_FOUND);
        $contractor->logModelActivity(ContractorActivityType::ContactDeleted->value, $contact);
        $contact->delete();

        return response()->json(['message' => 'Contact deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
