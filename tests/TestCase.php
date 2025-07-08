<?php

namespace Tests;

use Database\Seeders\TestDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    protected $seed = true;

    protected $seeder = TestDatabaseSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession([]);
    }
}
