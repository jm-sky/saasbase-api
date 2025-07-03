<?php

namespace Tests\Unit\Approval;

use App\Domain\Approval\Services\ApprovalResolutionService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ApprovalResolutionServiceSimpleTest extends TestCase
{
    private ApprovalResolutionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ApprovalResolutionService();
    }

    #[Test]
    public function itCanBeInstantiated()
    {
        $this->assertInstanceOf(ApprovalResolutionService::class, $this->service);
    }

    #[Test]
    public function itHasRequiredPublicMethods()
    {
        $this->assertTrue(method_exists($this->service, 'resolveApprovers'));
        $this->assertTrue(method_exists($this->service, 'userHasSystemPermission'));
        $this->assertTrue(method_exists($this->service, 'getAllAvailableApprovers'));
    }
}
