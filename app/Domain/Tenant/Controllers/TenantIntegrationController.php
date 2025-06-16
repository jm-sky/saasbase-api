<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Tenant\Models\TenantIntegration;
use App\Domain\Tenant\Requests\StoreTenantIntegrationRequest;
use App\Domain\Tenant\Requests\UpdateTenantIntegrationRequest;
use App\Domain\Tenant\Resources\TenantIntegrationResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class TenantIntegrationController extends Controller
{
    use HasIndexQuery;

    public function __construct()
    {
        $this->modelClass = TenantIntegration::class;
        $this->filters    = [
            AllowedFilter::custom('search', new ComboSearchFilter(['type'])),
            AllowedFilter::custom('id', new AdvancedFilter()),
            AllowedFilter::exact('type'),
            AllowedFilter::exact('enabled'),
            AllowedFilter::exact('mode'),
        ];
        $this->sorts       = ['created_at', 'updated_at', 'type', 'mode'];
        $this->defaultSort = '-created_at';
    }

    /**
     * Display a listing of the tenant's integrations.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user  = Auth::user();
        $query = $this->getIndexQuery($request);
        $query->where('tenant_id', $user->getTenantId());

        return TenantIntegrationResource::collection($query->paginate());
    }

    /**
     * Store a newly created integration.
     */
    public function store(StoreTenantIntegrationRequest $request): TenantIntegrationResource
    {
        /** @var User $user */
        $user        = Auth::user();
        $integration = DB::transaction(function () use ($request, $user) {
            return $user->currentTenant()->integrations()->create($request->validated());
        });

        return new TenantIntegrationResource($integration);
    }

    /**
     * Display the specified integration.
     */
    public function show(TenantIntegration $integration): TenantIntegrationResource
    {
        $this->authorize('view', $integration);

        return new TenantIntegrationResource($integration);
    }

    /**
     * Update the specified integration.
     */
    public function update(UpdateTenantIntegrationRequest $request, TenantIntegration $integration): TenantIntegrationResource
    {
        $this->authorize('update', $integration);

        $integration->update($request->validated());

        return new TenantIntegrationResource($integration);
    }

    /**
     * Remove the specified integration.
     */
    public function destroy(string $integrationId): JsonResponse
    {
        /** @var User $user */
        $user        = Auth::user();
        $integration = $user->currentTenant()->integrations()->findOrFail($integrationId);

        $this->authorize('delete', $integration);

        $integration->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
