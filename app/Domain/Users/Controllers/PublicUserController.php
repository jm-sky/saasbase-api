<?php

namespace App\Domain\Users\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Resources\UserPreviewResource;
use App\Domain\Common\Resources\UserProfileTenantScopedResource;
use App\Domain\Tenant\Models\Tenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class PublicUserController extends Controller
{
    protected int $defaultPerPage = 15;

    protected ?Tenant $tenant;

    protected function getTenant()
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Tenant $tenant */
        $tenant = $user->tenants()->firstWhere('tenants.id', $user->getTenantId());

        $this->tenant = $tenant;
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->getTenant();

        $users = $this->tenant->users()->get();

        return UserPreviewResource::collection($users);
    }

    public function show(User $user): UserProfileTenantScopedResource
    {
        return new UserProfileTenantScopedResource($user);
    }
}
