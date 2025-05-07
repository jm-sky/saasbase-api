<?php

namespace App\Domain\Tenant\Actions;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Traits\RespondsWithToken;
use App\Domain\Tenant\Exceptions\TenantNotFoundException;
use App\Domain\Tenant\Exceptions\UserNotBelongToTenantException;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\Response;

class GenerateTenantJwtAction
{
    use AsAction;
    use RespondsWithToken;

    public function handle(User $user, Tenant $tenant): string
    {
        return JwtHelper::createTokenWithTenant($user, $tenant->id);
    }

    public function asController(Tenant $tenant): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $token = $this->handle($user, $tenant);

            return $this->respondWithToken($token, $user, tenantId: $tenant->id);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @throws TenantNotFoundException
     * @throws UserNotBelongToTenantException
     * @throws \RuntimeException
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Tenant $tenant */
        $tenant = request()->route('tenant');

        // Verify user is authenticated
        if (!$user) {
            throw new \RuntimeException('User not authenticated');
        }

        // Verify tenant exists and is active
        if (!$tenant->exists || null !== $tenant->deleted_at) {
            throw new TenantNotFoundException('Tenant not found or inactive');
        }

        // Verify user belongs to tenant
        if (!$user->tenants()->find($tenant->id)) {
            throw new UserNotBelongToTenantException();
        }

        return true;
    }
}
