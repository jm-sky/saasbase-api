<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Contractors\DTO\ContractorDTO;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Requests\ContractorRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ContractorController extends Controller
{
    public function index(): JsonResponse
    {
        $contractors = Contractor::paginate();
        return response()->json(
            ContractorDTO::collect($contractors)
        );
    }

    public function store(ContractorRequest $request): JsonResponse
    {
        $dto = ContractorDTO::from($request->validated());
        $contractor = Contractor::create((array) $dto);

        return response()->json(
            ContractorDTO::from($contractor),
            Response::HTTP_CREATED
        );
    }

    public function show(Contractor $contractor): JsonResponse
    {
        return response()->json(
            ContractorDTO::from($contractor)
        );
    }

    public function update(ContractorRequest $request, Contractor $contractor): JsonResponse
    {
        $dto = ContractorDTO::from($request->validated());
        $contractor->update((array) $dto);

        return response()->json(
            ContractorDTO::from($contractor)
        );
    }

    public function destroy(Contractor $contractor): JsonResponse
    {
        $contractor->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
