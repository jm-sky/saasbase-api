<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Domain\Tenant\Models\PositionCategory;
use App\Domain\Tenant\Requests\StorePositionCategoryRequest;
use App\Domain\Tenant\Requests\UpdatePositionCategoryRequest;
use App\Domain\Tenant\Resources\PositionCategoryResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PositionCategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = PositionCategory::query()
            ->ordered()
            ->get()
        ;

        return PositionCategoryResource::collection($categories);
    }

    public function store(StorePositionCategoryRequest $request): PositionCategoryResource
    {
        $this->authorizeManage();

        $data     = $request->validated();
        $category = PositionCategory::create($data);

        return new PositionCategoryResource($category);
    }

    public function update(UpdatePositionCategoryRequest $request, PositionCategory $positionCategory): PositionCategoryResource
    {
        $this->authorizeManage();

        $positionCategory->update($request->validated());

        return new PositionCategoryResource($positionCategory);
    }

    public function destroy(PositionCategory $positionCategory): Response
    {
        $this->authorizeManage();

        $positionCategory->delete();

        return response()->noContent();
    }

    /**
     * Position categories drive org-chart/RBAC structure, so — like
     * OrganizationUnitController — writes are restricted to Owner/Admin.
     * Tenant isolation itself is already enforced by PositionCategory's
     * IsGlobalOrBelongsToTenant global scope (route-model binding 404s
     * for a category outside the current tenant), so no manual tenant_id
     * comparison is needed here.
     */
    private function authorizeManage(): void
    {
        /** @var User $user */
        $user     = Auth::user();
        $tenantId = $user->getTenantId();

        abort_unless(
            $tenantId && TenantScopedRoles::userHasAnyRole($user, $tenantId, [RoleName::Owner->value, RoleName::Admin->value]),
            Response::HTTP_FORBIDDEN
        );
    }
}
