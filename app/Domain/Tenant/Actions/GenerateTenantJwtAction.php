<?php

namespace App\Domain\Tenant\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class GenerateTenantJwtAction
{
    use AsAction;

    public function handle(User $user, Tenant $tenant): string
    {
        $this->authorize($user, $tenant);

        // Create custom claims with tenant context
        $customClaims = [
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
            'user_role' => $user->tenants()->where('tenant_id', $tenant->id)->first()?->pivot->role
        ];

        // Generate token with custom claims
        return JWTAuth::claims($customClaims)->fromUser($user);
    }

    public function asController(Tenant $tenant): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $token = $this->handle($user, $tenant);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @throws RuntimeException
     */
    private function authorize(User $user, Tenant $tenant): void
    {
        // Verify user is authenticated
        if (!$user) {
            throw new RuntimeException('User not authenticated');
        }

        // Verify tenant exists and is active
        if (!$tenant->exists || $tenant->deleted_at !== null) {
            throw new RuntimeException('Tenant not found or inactive');
        }

        // Verify user belongs to tenant
        if (!$user->tenants()->where('tenant_id', $tenant->id)->exists()) {
            throw new RuntimeException('User does not belong to this tenant');
        }
    }

    public function rules(): array
    {
        return [
            'tenant' => [
                'required',
                'uuid',
                'exists:tenants,id,deleted_at,NULL'
            ]
        ];
    }
}
