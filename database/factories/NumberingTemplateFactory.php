<?php

namespace Database\Factories;

use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Invoice\Enums\ResetPeriod;
use App\Domain\Invoice\Models\NumberingTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NumberingTemplateFactory extends Factory
{
    protected $model = NumberingTemplate::class;

    public function definition(): array
    {
        return [
            'id'           => (string) Str::ulid(),
            'tenant_id'    => null, // Set in test or use Tenant factory if needed
            'name'         => $this->faker->words(2, true),
            'invoice_type' => InvoiceType::Basic,
            'format'       => 'INV-YYYY-MM-NNNN',
            'next_number'  => 1,
            'reset_period' => ResetPeriod::YEARLY,
            'prefix'       => '',
            'suffix'       => '',
            'is_default'   => false,
        ];
    }
}
