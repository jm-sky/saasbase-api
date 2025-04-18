<?php

namespace Tests\Feature\Database\Seeders;

use App\Domain\Common\Models\VatRate;
use Database\Seeders\VatRateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VatRateSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_vat_rate_seeder_creates_expected_records(): void
    {
        $this->seed(VatRateSeeder::class);

        $this->assertDatabaseCount('vat_rates', 5);
        $this->assertDatabaseHas('vat_rates', [
            'name' => 'Standard rate',
            'rate' => 23.00,
        ]);
        $this->assertDatabaseHas('vat_rates', [
            'name' => 'Reduced rate',
            'rate' => 8.00,
        ]);
        $this->assertDatabaseHas('vat_rates', [
            'name' => 'Super-reduced rate',
            'rate' => 5.00,
        ]);
        $this->assertDatabaseHas('vat_rates', [
            'name' => 'Zero rate',
            'rate' => 0.00,
        ]);
        $this->assertDatabaseHas('vat_rates', [
            'name' => 'Exempt',
            'rate' => 0.00,
        ]);
    }
}
