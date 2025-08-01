<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\OrganizationUnit;
use App\Domain\Tenant\Models\Position;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\StoreOrganizationUnitRequest;
use App\Domain\Tenant\Resources\OrganizationUnitResource;
use App\Domain\Tenant\Services\OrganizationPositionService;
use App\Domain\Tenant\Support\TenantIdResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrganizationUnitController extends Controller
{
    private Tenant $tenant;

    private OrganizationPositionService $organizationPositionService;

    public function __construct()
    {
        $this->tenant                      = Tenant::find(TenantIdResolver::resolve());
        $this->organizationPositionService = new OrganizationPositionService($this->tenant);
    }

    public function index(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user     = Auth::user();
        $tenantId = $user->tenant_id;

        $units = OrganizationUnit::query()
            ->with('activeUsers', 'parent', 'positions')
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

    public function show(string $tenantId, string $unitId): OrganizationUnitResource
    {
        $unit = OrganizationUnit::where('tenant_id', $tenantId)
            ->where('id', $unitId)
            ->firstOrFail()
        ;

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

    public function assignUserToUnit(Request $request, string $tenantId, string $unitId): JsonResponse
    {
        /** @var OrganizationUnit $unit */
        $unit     = $this->tenant->organizationUnits()->findOrFail($unitId);
        /** @var User $user */
        $user     = $this->tenant->users()->findOrFail($request->input('userId'));
        /** @var Position $position */
        $position = $unit->positions()->findOrFail($request->input('positionId'));

        $this->organizationPositionService->assignUserToPosition($user, $unit, $position);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
