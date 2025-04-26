<?php

namespace Tests\Traits;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Payload;

trait WithMockedJwtPayload
{
    protected function mockJwtPayload(array $claims = []): void
    {
        $payloadMock = \Mockery::mock(Payload::class);

        $payloadMock->shouldReceive('get')
            ->andReturnUsing(function (string $key) use ($claims) {
                return $claims[$key] ?? null;
            })
        ;

        JWTAuth::shouldReceive('payload')
            ->once()
            ->andReturn($payloadMock)
        ;
    }

    protected function mockTenantId(string $tenantId): void
    {
        $this->mockJwtPayload([
            'tenant_id' => $tenantId,
        ]);
    }
}
