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
            ->with('users', 'parent')
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

    public function destroy(string $tenantId, string $unitId): JsonResponse
    {
        $unit = OrganizationUnit::where('tenant_id', $tenantId)
            ->where('id', $unitId)
            ->firstOrFail()
        ;

        if ($unit->is_technical) {
            return response()->json(['message' => 'Cannot delete technical organization unit'], Response::HTTP_BAD_REQUEST);
        }

        if (!$unit->parent_id) {
            return response()->json(['message' => 'Cannot delete root organization unit'], Response::HTTP_BAD_REQUEST);
        }

        $unit->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
