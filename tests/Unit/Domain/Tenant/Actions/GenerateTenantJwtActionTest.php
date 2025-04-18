<?php

namespace Tests\Unit\Domain\Tenant\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Actions\GenerateTenantJwtAction;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @internal
 *
 * @coversNothing
 */
class GenerateTenantJwtActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up JWT for testing with a secure key (at least 256 bits)
        $this->app['config']->set('jwt.secret', 'NxJPKm5QnwS8JhKvXDmJRfbpCGfHLwkY12dvZEF9j3U='); // 256-bit key
        $this->app['config']->set('jwt.algo', 'HS256');
        $this->app['config']->set('jwt.ttl', 60);
    }

    public function testGeneratesJwtWithTenantContext(): void
    {
        // Arrange
        $user   = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $role   = 'admin';

        // Associate user with tenant
        $user->tenants()->attach($tenant, ['role' => $role]);

        // Act
        $token   = GenerateTenantJwtAction::run($user, $tenant);
        $payload = JWTAuth::setToken($token)->getPayload();

        // Assert
        $this->assertEquals($tenant->id, $payload->get('tenant_id'));
        $this->assertEquals($tenant->slug, $payload->get('tenant_slug'));
        $this->assertEquals($role, $payload->get('user_role'));
    }

    public function testThrowsExceptionWhenUserDoesNotBelongToTenant(): void
    {
        // Arrange
        $user   = User::factory()->create();
        $tenant = Tenant::factory()->create();

        // Assert & Act
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User does not belong to this tenant');

        GenerateTenantJwtAction::run($user, $tenant);
    }
}
