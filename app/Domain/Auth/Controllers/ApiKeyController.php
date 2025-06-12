<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\ApiKey;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Requests\StoreApiKeyRequest;
use App\Domain\Auth\Requests\UpdateApiKeyRequest;
use App\Domain\Auth\Resources\ApiKeyResource;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    use AuthorizesRequests;

    public function index(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = Auth::user();

        $apiKeys = $user->apiKeys()->where('tenant_id', $user->getTenantId())->get();

        return ApiKeyResource::collection($apiKeys);
    }

    public function store(StoreApiKeyRequest $request): ApiKeyResource
    {
        /** @var User $user */
        $user = Auth::user();

        $apiKey = ApiKey::create([
            'tenant_id'  => $user->getTenantId(),
            'user_id'    => $user->id,
            'name'       => $request->name,
            'key'        => Str::random(64),
            'scopes'     => $request->scopes,
            'is_active'  => true,
            'expires_at' => $request->expiresAt,
        ]);

        return new ApiKeyResource($apiKey);
    }

    public function show(ApiKey $apiKey): ApiKeyResource
    {
        $this->authorize('view', $apiKey);

        return new ApiKeyResource($apiKey);
    }

    public function update(UpdateApiKeyRequest $request, ApiKey $apiKey): ApiKeyResource
    {
        $this->authorize('update', $apiKey);

        $apiKey->update($request->validated());

        return new ApiKeyResource($apiKey);
    }

    public function revoke(ApiKey $apiKey): void
    {
        $this->authorize('update', $apiKey);
        $apiKey->update(['is_active' => false]);
    }

    public function destroy(ApiKey $apiKey): void
    {
        $this->authorize('delete', $apiKey);

        $apiKey->delete();
    }
}
