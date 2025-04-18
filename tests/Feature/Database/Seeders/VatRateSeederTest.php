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
            'name' => '23%',
            'rate' => 0.23,
        ]);
        $this->assertDatabaseHas('vat_rates', [
            'name' => '8%',
            'rate' => 0.08,
        ]);
        $this->assertDatabaseHas('vat_rates', [
            'name' => '5%',
            'rate' => 0.05,
        ]);
        $this->assertDatabaseHas('vat_rates', [
            'name' => '0%',
            'rate' => 0.00,
        ]);
        $this->assertDatabaseHas('vat_rates', [
            'name' => 'Exempt',
            'rate' => 0.00,
        ]);
    }
}
