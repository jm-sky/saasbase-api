<?php

namespace Tests\Unit\Domain\Tenant\Actions;

use App\Domain\Auth\Models\User;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Tenant\Actions\GenerateTenantJwtAction;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @internal
 */
#[CoversClass(GenerateTenantJwtAction::class)]
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
        $role   = RoleName::Admin->value;

        // Associate user with tenant
        $user->tenants()->attach($tenant, ['role' => $role]);

        // Act
        $token   = GenerateTenantJwtAction::run($user, $tenant);
        $payload = JWTAuth::setToken($token)->getPayload();

        // Assert
        $this->assertEquals($tenant->id, $payload->get('tid'));
    }
}
