<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Tenant\DTOs\TenantDTO;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\TenantRequest;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $tenants = $request->user()->tenants()->orderBy('created_at')->get();

        return response()->json([
            'data' => TenantDTO::collect($tenants),
        ]);
    }

    public function store(TenantRequest $request): JsonResponse
    {
        // TODO: Create enum for tenant roles
        $tenant = Tenant::create($request->validated());
        $request->user()->tenants()->attach($tenant, ['role' => 'admin']);

        return response()->json([
            'data' => TenantDTO::from($tenant),
        ], Response::HTTP_CREATED);
    }

    public function show(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('view', $tenant);

        return response()->json([
            'data' => TenantDTO::from($tenant),
        ]);
    }

    public function update(TenantRequest $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('update', $tenant);
        $tenant->update($request->validated());

        return response()->json([
            'data' => TenantDTO::from($tenant),
        ]);
    }

    public function destroy(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('delete', $tenant);
        $tenant->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
