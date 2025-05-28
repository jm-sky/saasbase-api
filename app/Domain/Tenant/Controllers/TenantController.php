<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Tenant\Enums\TenantActivityType;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\TenantRequest;
use App\Domain\Tenant\Resources\TenantPreviewResource;
use App\Domain\Tenant\Resources\TenantResource;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class TenantController extends Controller
{
    use AuthorizesRequests;
    use HasActivityLogging;

    public function index(Request $request): AnonymousResourceCollection
    {
        $tenants = $request->user()->tenants()->orderBy('created_at')->get();

        return TenantResource::collection($tenants);
    }

    public function indexPreview(Request $request): AnonymousResourceCollection
    {
        $tenants = $request->user()->tenants()->orderBy('created_at')->get();

        return TenantPreviewResource::collection($tenants);
    }

    public function store(TenantRequest $request): JsonResponse
    {
        // TODO: Create enum for tenant roles
        $tenant = Tenant::create($request->validated());
        $request->user()->tenants()->attach($tenant, ['role' => 'admin']);
        // We already log activity in Tenant model
        // $tenant->logModelActivity(TenantActivityType::Created->value, $tenant);

        return response()->json([
            'message' => 'Tenant created successfully.',
            'data'    => new TenantResource($tenant),
        ], Response::HTTP_CREATED);
    }

    public function show(Request $request, Tenant $tenant): TenantResource
    {
        $this->authorize('view', $tenant);

        return new TenantResource($tenant);
    }

    public function update(TenantRequest $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('update', $tenant);
        $tenant->update($request->validated());
        // We already log activity in Tenant model
        // $tenant->logModelActivity(TenantActivityType::Updated->value, $tenant);

        return response()->json([
            'message' => 'Tenant updated successfully.',
            'data'    => new TenantResource($tenant),
        ]);
    }

    public function destroy(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('delete', $tenant);
        // We already log activity in Tenant model
        // $tenant->logModelActivity(TenantActivityType::Deleted->value, $tenant);
        $tenant->delete();

        return response()->json(['message' => 'Tenant deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
