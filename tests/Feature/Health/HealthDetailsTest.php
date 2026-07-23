<?php

namespace Tests\Feature\Health;

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(HealthController::class)]
class HealthDetailsTest extends TestCase
{
    private const TEST_TOKEN = 'test-health-details-token';

    private const ENDPOINT = '/api/health/details';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('health.details_token', self::TEST_TOKEN);
        Config::set('app.frontend_url', 'http://127.0.0.1:9');
    }

    public function testHealthDetailsRequiresToken(): void
    {
        $this->getJson(self::ENDPOINT)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;
    }

    public function testHealthDetailsRejectsWrongToken(): void
    {
        $this->withHeader('Authorization', 'Bearer wrong-token')
            ->getJson(self::ENDPOINT)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;
    }

    public function testHealthDetailsRejectsWhenTokenUnconfigured(): void
    {
        Config::set('health.details_token', '');

        $this->withHeader('Authorization', 'Bearer anything')
            ->getJson(self::ENDPOINT)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;
    }

    public function testHealthDetailsReturnsSchemaWithValidToken(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . self::TEST_TOKEN)
            ->getJson(self::ENDPOINT)
        ;

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'schema_version',
                'status',
                'environment',
                'components' => [
                    'database' => ['status'],
                    'cache'    => ['status'],
                    'storage'  => ['status'],
                    'frontend' => ['status'],
                ],
            ])
        ;

        $data = $response->json();

        $this->assertSame(1, $data['schema_version']);
        $this->assertContains($data['status'], ['ok', 'degraded', 'failed']);

        foreach (['database', 'cache', 'storage', 'frontend'] as $component) {
            $this->assertContains(
                $data['components'][$component]['status'],
                ['ok', 'degraded', 'failed'],
            );
        }

        $this->assertSame('failed', $data['components']['frontend']['status']);
        $this->assertSame('ok', $data['components']['database']['status']);
    }
}
