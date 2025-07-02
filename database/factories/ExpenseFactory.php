<?php

namespace Database\Factories;

use App\Domain\Expense\Models\Expense;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Tenant\Models\Tenant;
use Brick\Math\BigDecimal;
use Database\Factories\DTOs\InvoiceBodyDTOFactory;
use Database\Factories\DTOs\InvoiceOptionsDTOFactory;
use Database\Factories\DTOs\InvoicePartyDTOFactory;
use Database\Factories\DTOs\InvoicePaymentDTOFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $totalNet   = BigDecimal::of(fake()->randomFloat(2, 100, 1000));
        $totalTax   = $totalNet->multipliedBy('0.23'); // 23% VAT
        $totalGross = $totalNet->plus($totalTax);

        return [
            'id'                => Str::ulid()->toString(),
            'tenant_id'         => Tenant::factory(),
            'type'              => fake()->randomElement(InvoiceType::cases()),
            'general_status'    => fake()->randomElement(InvoiceStatus::cases()),
            'ocr_status'        => null,
            'allocation_status' => null,
            'approval_status'   => null,
            'delivery_status'   => null,
            'payment_status'    => null,
            'number'            => fake()->unique()->numerify('INV-####'),
            'total_net'         => $totalNet,
            'total_tax'         => $totalTax,
            'total_gross'       => $totalGross,
            'currency'          => fake()->currencyCode(),
            'exchange_rate'     => BigDecimal::of('1.0'),
            'issue_date'        => fake()->dateTimeBetween('-1 year', 'now'),
            'seller'            => (new InvoicePartyDTOFactory())->make(),
            'buyer'             => (new InvoicePartyDTOFactory())->make(),
            'body'              => (new InvoiceBodyDTOFactory())->make(),
            'payment'           => (new InvoicePaymentDTOFactory())->make(),
            'options'           => (new InvoiceOptionsDTOFactory())->make(),
        ];
    }

    public function draft(): self
    {
        return $this->state(fn (array $attributes) => [
            'general_status' => InvoiceStatus::DRAFT->value,
        ]);
    }

    public function sent(): self
    {
        return $this->state(fn (array $attributes) => [
            'general_status'  => InvoiceStatus::ACTIVE->value,
            'delivery_status' => \App\Domain\Financial\Enums\DeliveryStatus::SENT->value,
        ]);
    }

    public function paid(): self
    {
        return $this->state(fn (array $attributes) => [
            'general_status' => InvoiceStatus::COMPLETED->value,
            'payment_status' => \App\Domain\Financial\Enums\PaymentStatus::PAID->value,
            'payment'        => (new InvoicePaymentDTOFactory())->paid(),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $attributes) => [
            'general_status' => InvoiceStatus::CANCELLED->value,
        ]);
    }
}
