<?php

namespace Database\Factories;

use App\Domain\Expense\Models\Expense;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Tenant\Models\Tenant;
use Brick\Math\BigDecimal;
use Database\Factories\DTOs\InvoiceBodyDTOFactory;
use Database\Factories\DTOs\InvoiceExchangeDTOFactory;
use Database\Factories\DTOs\InvoiceLineDTOFactory;
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
            'general_status'  => InvoiceStatus::ISSUED->value,
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

    public function withSeller(array $sellerData): self
    {
        return $this->state(fn (array $attributes) => [
            'seller' => (new InvoicePartyDTOFactory())->make($sellerData),
        ]);
    }

    public function withBuyer(array $buyerData): self
    {
        return $this->state(fn (array $attributes) => [
            'buyer' => (new InvoicePartyDTOFactory())->make($buyerData),
        ]);
    }

    public function withBody(array $bodyData): self
    {
        return $this->state(fn (array $attributes) => [
            'body' => (new InvoiceBodyDTOFactory())->make($bodyData),
        ]);
    }

    public function withPayment(array $paymentData): self
    {
        return $this->state(fn (array $attributes) => [
            'payment' => (new InvoicePaymentDTOFactory())->make($paymentData),
        ]);
    }

    public function withOptions(array $optionsData): self
    {
        return $this->state(fn (array $attributes) => [
            'options' => (new InvoiceOptionsDTOFactory())->make($optionsData),
        ]);
    }

    /**
     * @param \DateTime|string      $issueDate
     * @param \DateTime|string|null $dueDate
     */
    public function withDates($issueDate, $dueDate = null): self
    {
        $state = ['issue_date' => $issueDate];

        if (null !== $dueDate) {
            // Assuming there's a due_date field in the DTO or payment section
            $paymentData      = ['due_date' => $dueDate];
            $state['payment'] = (new InvoicePaymentDTOFactory())->make($paymentData);
        }

        return $this->state(fn (array $attributes) => $state);
    }

    public function receivedFromBp(Tenant $tenant): self
    {
        // Add randomness to pricing
        $basePrice1 = fake()->numberBetween(12000, 18000); // 12k-18k per month
        $quantity1  = fake()->numberBetween(2, 4);         // 2-4 months
        $total1     = $basePrice1 * $quantity1;

        return $this->state(fn (array $attributes) => [
            'currency' => 'PLN',
            'seller'   => (new InvoicePartyDTOFactory())->make([
                'contractorType' => 'company',
                'name'           => 'BP Polska Sp. z o.o.',
                'address'        => 'ul. Pawia 9, 31-154 Kraków',
                'country'        => 'PL',
                'contractorId'   => null,
                'taxId'          => 'PL9720865431',
                'iban'           => null,
                'email'          => 'bp@bp.com',
            ]),
            'buyer' => (new InvoicePartyDTOFactory())->make([
                'contractorType' => 'company',
                'name'           => $tenant->name,
                'address'        => $tenant->defaultAddress?->full_address ?? $tenant->addresses->first()?->full_address ?? 'Address not set',
                'country'        => $tenant->country ?? 'PL',
                'contractorId'   => null,
                'taxId'          => $tenant->vat_id ?? $tenant->tax_id ?? fake()->numerify('##########'),
                'iban'           => null,
                'email'          => $tenant->email ?? fake()->email(),
            ]),
            'body' => (new InvoiceBodyDTOFactory())->make([
                'lines' => [
                    (new InvoiceLineDTOFactory())->make([
                        'id'          => Str::ulid()->toString(),
                        'description' => 'Paliwo za delegację do Krakowa',
                        'quantity'    => BigDecimal::of($quantity1),
                        'unitPrice'   => BigDecimal::of($basePrice1),
                        'vatRate'     => new \App\Domain\Financial\DTOs\VatRateDTO(
                            id: Str::ulid()->toString(),
                            name: '23% VAT',
                            rate: 23,
                            type: \App\Domain\Common\Enums\VatRateType::PERCENTAGE
                        ),
                        'totalNet'   => BigDecimal::of($total1),
                        'totalVat'   => BigDecimal::of($total1 * 0.23),
                        'totalGross' => BigDecimal::of($total1 + ($total1 * 0.23)),
                        'productId'  => null,
                    ]),
                ],
                'exchange' => (new InvoiceExchangeDTOFactory())->make([
                    'currency'     => 'PLN',
                    'exchangeRate' => BigDecimal::of('1.0'),
                    'date'         => now()->toDateString(),
                ]),
                'description' => 'Services provided under Contract #BP-2024-SB-001. Payment due within 30 days.',
            ]),
            'payment' => (new InvoicePaymentDTOFactory())->make([
                'status'     => \App\Domain\Financial\Enums\PaymentStatus::PENDING,
                'dueDate'    => null, // Will be set by withDates method
                'paidDate'   => null,
                'paidAmount' => BigDecimal::of('0'),
                'method'     => \App\Domain\Financial\Enums\PaymentMethod::CREDIT_CARD,
                'reference'  => 'BP-EXP-2024-001',
                'terms'      => 'Net 30',
                'notes'      => 'Expense from BP services',
            ]),
        ]);
    }

    public function receivedFromNasa(Tenant $tenant): self
    {
        // Add randomness to pricing
        $basePrice1 = fake()->numberBetween(12000, 18000); // 12k-18k per month
        $quantity1  = fake()->numberBetween(2, 4);         // 2-4 months
        $total1     = $basePrice1 * $quantity1;

        $basePrice2 = fake()->numberBetween(20000, 30000); // 20k-30k

        $basePrice3 = fake()->numberBetween(400, 600);     // 400-600 per hour
        $quantity3  = fake()->numberBetween(30, 50);       // 30-50 hours
        $total3     = $basePrice3 * $quantity3;

        $totalNet   = $total1 + $basePrice2 + $total3;
        $vatRate    = 0.23;
        $totalVat   = $totalNet * $vatRate;
        $totalGross = $totalNet + $totalVat;

        return $this->state(fn (array $attributes) => [
            'currency' => 'PLN',
            'seller'   => (new InvoicePartyDTOFactory())->make([
                'contractorType' => 'company',
                'name'           => 'National Aeronautics and Space Administration',
                'address'        => '300 E Street SW, Washington, DC 20546',
                'country'        => 'US',
                'contractorId'   => null,
                'taxId'          => 'US-NASA-2024',
                'iban'           => null,
                'email'          => 'procurement@nasa.gov',
            ]),
            'buyer' => (new InvoicePartyDTOFactory())->make([
                'contractorType' => 'company',
                'name'           => $tenant->name,
                'address'        => $tenant->defaultAddress?->full_address ?? $tenant->addresses->first()?->full_address ?? 'Address not set',
                'country'        => 'PL',
                'contractorId'   => null,
                'taxId'          => $tenant->vat_id ?? $tenant->tax_id ?? fake()->numerify('##########'),
                'iban'           => null,
                'email'          => $tenant->email ?? fake()->email(),
            ]),
            'body' => (new InvoiceBodyDTOFactory())->make([
                'lines' => [
                    (new InvoiceLineDTOFactory())->make([
                        'id'          => Str::ulid()->toString(),
                        'description' => 'Monthly cloud infrastructure hosting and satellite data storage services',
                        'quantity'    => BigDecimal::of($quantity1),
                        'unitPrice'   => BigDecimal::of($basePrice1),
                        'vatRate'     => new \App\Domain\Financial\DTOs\VatRateDTO(
                            id: Str::ulid()->toString(),
                            name: '23% VAT',
                            rate: 23,
                            type: \App\Domain\Common\Enums\VatRateType::PERCENTAGE
                        ),
                        'totalNet'   => BigDecimal::of($total1),
                        'totalVat'   => BigDecimal::of($total1 * $vatRate),
                        'totalGross' => BigDecimal::of($total1 * (1 + $vatRate)),
                        'productId'  => null,
                    ]),
                    (new InvoiceLineDTOFactory())->make([
                        'id'          => Str::ulid()->toString(),
                        'description' => 'Enterprise software licensing for data processing tools',
                        'quantity'    => BigDecimal::of(1),
                        'unitPrice'   => BigDecimal::of($basePrice2),
                        'vatRate'     => new \App\Domain\Financial\DTOs\VatRateDTO(
                            id: Str::ulid()->toString(),
                            name: '23% VAT',
                            rate: 23,
                            type: \App\Domain\Common\Enums\VatRateType::PERCENTAGE
                        ),
                        'totalNet'   => BigDecimal::of($basePrice2),
                        'totalVat'   => BigDecimal::of($basePrice2 * $vatRate),
                        'totalGross' => BigDecimal::of($basePrice2 * (1 + $vatRate)),
                        'productId'  => null,
                    ]),
                    (new InvoiceLineDTOFactory())->make([
                        'id'          => Str::ulid()->toString(),
                        'description' => 'Specialized consulting services for mission-critical applications',
                        'quantity'    => BigDecimal::of($quantity3),
                        'unitPrice'   => BigDecimal::of($basePrice3),
                        'vatRate'     => new \App\Domain\Financial\DTOs\VatRateDTO(
                            id: Str::ulid()->toString(),
                            name: '23% VAT',
                            rate: 23,
                            type: \App\Domain\Common\Enums\VatRateType::PERCENTAGE
                        ),
                        'totalNet'   => BigDecimal::of($total3),
                        'totalVat'   => BigDecimal::of($total3 * $vatRate),
                        'totalGross' => BigDecimal::of($total3 * (1 + $vatRate)),
                        'productId'  => null,
                    ]),
                ],
                'exchange' => (new InvoiceExchangeDTOFactory())->make([
                    'currency'     => 'PLN',
                    'exchangeRate' => BigDecimal::of('1.0'),
                    'date'         => now()->toDateString(),
                ]),
                'description' => 'Services provided under Contract #NASA-2024-SB-001. Payment due within 30 days.',
            ]),
            'payment' => (new InvoicePaymentDTOFactory())->make([
                'status'     => \App\Domain\Financial\Enums\PaymentStatus::PENDING,
                'dueDate'    => null, // Will be set by withDates method
                'paidDate'   => null,
                'paidAmount' => BigDecimal::of('0'),
                'method'     => \App\Domain\Financial\Enums\PaymentMethod::BANK_TRANSFER,
                'reference'  => 'NASA-EXP-2024-001',
                'terms'      => 'Net 30',
                'notes'      => 'Expense from NASA services',
            ]),
            'options' => (new InvoiceOptionsDTOFactory())->make([
                'language'  => 'en',
                'template'  => 'standard',
                'sendEmail' => false,
                'emailTo'   => [$tenant->email ?? fake()->email()],
            ]),
        ]);
    }
}
