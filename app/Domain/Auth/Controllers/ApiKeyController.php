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

        $apiKeys = ApiKey::where('user_id', $user->id)
            ->where('tenant_id', $user->getCurrentTenantId())
            ->get()
        ;

        return ApiKeyResource::collection($apiKeys);
    }

    public function store(StoreApiKeyRequest $request): ApiKeyResource
    {
        /** @var User $user */
        $user = Auth::user();

        $apiKey = ApiKey::create([
            'tenant_id' => $user->getCurrentTenantId(),
            'user_id'   => $user->id,
            'name'      => $request->name,
            'key'       => Str::random(64),
            'scopes'    => $request->scopes,
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

    public function destroy(ApiKey $apiKey): void
    {
        $this->authorize('delete', $apiKey);

        $apiKey->delete();
    }
}
