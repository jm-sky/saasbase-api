<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Requests\StoreOrganizationUnitRequest;
use App\Domain\Tenant\Resources\OrganizationUnitResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrganizationUnitController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user     = Auth::user();
        $tenantId = $user->tenant_id;

        $units = OrganizationUnit::query()
            ->with('users')
            ->where('tenant_id', $tenantId)
            ->get()
        ;

        return OrganizationUnitResource::collection($units);
    }

    public function store(StoreOrganizationUnitRequest $request): OrganizationUnitResource
    {
        $unit = OrganizationUnit::create($request->validated());

        return new OrganizationUnitResource($unit);
    }

    public function show(OrganizationUnit $unit): OrganizationUnitResource
    {
        return new OrganizationUnitResource($unit);
    }

    public function update(StoreOrganizationUnitRequest $request, OrganizationUnit $unit): OrganizationUnitResource
    {
        $unit->update($request->validated());

        return new OrganizationUnitResource($unit);
    }

    public function destroy(OrganizationUnit $unit): JsonResponse
    {
        $unit->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
