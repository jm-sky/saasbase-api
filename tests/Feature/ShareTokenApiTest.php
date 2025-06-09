<?php

namespace Tests\Feature;

use App\Domain\ShareToken\Services\ShareTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(ShareTokenService::class)]
class ShareTokenApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $baseUrl = '/api/v1';

    public function testCanAccessShareTokenLink()
    {
        $this->markTestSkipped('Need to fix this test');

        $service = new ShareTokenService();
        $token   = $service->createToken('App\Domain\Invoice\Models\Invoice', 'invoice-id-1');

        $response = $this->get("{$this->baseUrl}/share/{$token->token}");
        $response->assertOk();
        $response->assertJsonFragment(['id' => $token->id]);
    }

    public function testExpiredTokenReturns403()
    {
        $service = new ShareTokenService();
        $token   = $service->createToken('App\Domain\Invoice\Models\Invoice', 'invoice-id-1', false, Carbon::now()->subDay());

        $response = $this->get("{$this->baseUrl}/share/{$token->token}");
        $response->assertForbidden();
    }

    public function testUsageLimitReturns403()
    {
        $service            = new ShareTokenService();
        $token              = $service->createToken('App\Domain\Invoice\Models\Invoice', 'invoice-id-1', false, null, 1);
        $token->usage_count = 1;
        $token->save();

        $response = $this->get("{$this->baseUrl}/share/{$token->token}");
        $response->assertForbidden();
    }

    public function testRequiresAuthenticationIfFlagged()
    {
        $service = new ShareTokenService();
        $token   = $service->createToken('App\Domain\Invoice\Models\Invoice', 'invoice-id-1', true);

        $response = $this->get("{$this->baseUrl}/share/{$token->token}");
        $response->assertForbidden();
    }
}
