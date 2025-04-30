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

        JWTAuth::shouldReceive('getToken')
            ->andReturn('mocked-jwt-token')
        ;

        JWTAuth::shouldReceive('setToken')
            ->with('mocked-jwt-token')
            ->andReturnSelf()
        ;

        JWTAuth::shouldReceive('setRequest')
            ->andReturnSelf()
        ;

        JWTAuth::shouldReceive('parser')
            ->andReturnSelf()
        ;

        JWTAuth::shouldReceive('parseToken')
            ->andReturnSelf()
        ;

        JWTAuth::shouldReceive('authenticate')
            ->andReturn(true)
        ;

        JWTAuth::shouldReceive('payload')
            ->andReturn($payloadMock)
        ;
    }

    protected function mockTenantId(string $tenantId): void
    {
        $this->mockJwtPayload([
            'tid' => $tenantId,
        ]);
    }
}
