<?php

namespace Tests\Unit\Services\IbanInfo;

use App\Services\IbanInfo\IbanInfoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(IbanInfoService::class)]
class IbanInfoServiceTest extends TestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        $this->markTestSkipped('This test is not implemented');
    }
}
