<?php

namespace Database\Factories;

use App\Domain\Contractors\Models\Contractor;
use App\Domain\Financial\DTOs\InvoicePaymentBankAccountDTO;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Products\Models\Product;
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
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $totalNet   = BigDecimal::of(fake()->randomFloat(2, 100, 1000));
        $totalTax   = $totalNet->multipliedBy('0.23'); // 23% VAT
        $totalGross = $totalNet->plus($totalTax);

        return [
            'id'                    => Str::ulid()->toString(),
            'tenant_id'             => Tenant::factory(),
            'type'                  => fake()->randomElement(InvoiceType::cases()),
            'general_status'        => fake()->randomElement(InvoiceStatus::cases()),
            'ocr_status'            => null,
            'allocation_status'     => null,
            'approval_status'       => null,
            'delivery_status'       => null,
            'payment_status'        => null,
            'number'                => fake()->unique()->numerify('2025/####'),
            'numbering_template_id' => null, // Will be set when creating
            'total_net'             => $totalNet,
            'total_tax'             => $totalTax,
            'total_gross'           => $totalGross,
            'currency'              => fake()->currencyCode(),
            'exchange_rate'         => BigDecimal::of('1.0'),
            'issue_date'            => fake()->dateTimeBetween('-1 year', 'now'),
            'seller'                => (new InvoicePartyDTOFactory())->make(),
            'buyer'                 => (new InvoicePartyDTOFactory())->make(),
            'body'                  => (new InvoiceBodyDTOFactory())->make(),
            'payment'               => (new InvoicePaymentDTOFactory())->make(),
            'options'               => (new InvoiceOptionsDTOFactory())->make(),
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

    public function soldServicesToContractor(Tenant $tenant, Contractor $contractor, ?Product $product = null): self
    {
        // Add randomness to base prices (±20%)
        $basePrice1 = fake()->numberBetween(100000, 150000); // 100k-150k instead of fixed 125k

        $totalNet   = $basePrice1;
        $totalTax   = $totalNet * 0.23;
        $totalGross = $totalNet + $totalTax;

        return $this->state(fn (array $attributes) => [
            'currency' => 'PLN',
            'seller'   => (new InvoicePartyDTOFactory())->make([
                'contractorType' => 'company',
                'name'           => $tenant->name,
                'address'        => $tenant->defaultAddress?->full_address ?? $tenant->addresses->first()?->full_address ?? 'You know our address',
                'country'        => 'PL',
                'contractorId'   => null,
                'taxId'          => $tenant->vat_id ?? $tenant->tax_id,
                'iban'           => null,
                'email'          => $tenant->email,
            ]),
            'buyer' => (new InvoicePartyDTOFactory())->make([
                'contractorType' => 'company',
                'name'           => $contractor->name,
                'address'        => $contractor->defaultAddress?->full_address ?? $contractor->addresses->first()?->full_address ?? 'Random street 123, PL',
                'country'        => $contractor->country,
                'contractorId'   => $contractor->id,
                'taxId'          => $contractor->vat_id ?? $contractor->tax_id,
                'iban'           => null,
                'email'          => $contractor->email,
            ]),
            'body' => (new InvoiceBodyDTOFactory())->make([
                'lines' => [
                    (new InvoiceLineDTOFactory())->make([
                        'id'          => Str::ulid()->toString(),
                        'description' => $product?->name ?? 'IT services',
                        'quantity'    => BigDecimal::of(1),
                        'unitPrice'   => BigDecimal::of($basePrice1),
                        'vatRate'     => new \App\Domain\Financial\DTOs\VatRateDTO(
                            id: Str::ulid()->toString(),
                            name: '23% VAT',
                            rate: 23,
                            type: \App\Domain\Common\Enums\VatRateType::PERCENTAGE
                        ),
                        'totalNet'   => BigDecimal::of($basePrice1),
                        'totalVat'   => BigDecimal::of($totalTax),
                        'totalGross' => BigDecimal::of($totalGross),
                        'productId'  => $product?->id,
                    ]),
                ],
                'exchange' => (new InvoiceExchangeDTOFactory())->make([
                    'currency'     => 'PLN',
                    'exchangeRate' => BigDecimal::of('1.0'),
                    'date'         => now()->toDateString(),
                ]),
                'description' => '',
            ]),
            'payment' => (new InvoicePaymentDTOFactory())->make([
                'status'      => \App\Domain\Financial\Enums\PaymentStatus::PENDING,
                'dueDate'     => null, // Will be set by withDates method
                'paidDate'    => null,
                'paidAmount'  => BigDecimal::of('0'),
                'method'      => \App\Domain\Financial\Enums\PaymentMethod::BANK_TRANSFER,
                'terms'       => '',
                'notes'       => '',
                'bankAccount' => $contractor->defaultBankAccount
                    ? InvoicePaymentBankAccountDTO::fromArray($contractor->defaultBankAccount->toArray())
                    : null,
            ]),
            'options' => (new InvoiceOptionsDTOFactory())->make([
                'sendEmail' => false,
                'emailTo'   => [$contractor->email],
            ]),
        ]);
    }

    public function soldServicesToNasa(Tenant $tenant): self
    {
        // Add randomness to base prices (±20%)
        $basePrice1 = fake()->numberBetween(100000, 150000); // 100k-150k instead of fixed 125k
        $basePrice2 = fake()->numberBetween(60000, 90000);   // 60k-90k instead of fixed 75k
        $basePrice3 = fake()->numberBetween(4000, 6000);     // 4k-6k per month instead of fixed 5k

        $quantity3 = fake()->numberBetween(6, 18); // 6-18 months instead of fixed 12
        $total3    = $basePrice3 * $quantity3;

        $totalNet   = $basePrice1 + $basePrice2 + $total3;
        $totalTax   = 0; // Government contract - tax exempt
        $totalGross = $totalNet;

        return $this->state(fn (array $attributes) => [
            'currency' => 'PLN',
            'seller'   => (new InvoicePartyDTOFactory())->make([
                'contractorType' => 'company',
                'name'           => $tenant->name,
                'address'        => $tenant->defaultAddress?->full_address ?? $tenant->addresses->first()?->full_address ?? 'You know our address',
                'country'        => 'PL',
                'contractorId'   => null,
                'taxId'          => $tenant->vat_id ?? $tenant->tax_id,
                'iban'           => null,
                'email'          => $tenant->email,
            ]),
            'buyer' => (new InvoicePartyDTOFactory())->make([
                'contractorType' => 'company',
                'name'           => 'National Aeronautics and Space Administration',
                'address'        => '300 E Street SW, Washington, DC 20546',
                'country'        => 'US',
                'contractorId'   => null,
                'taxId'          => 'US-NASA-2024',
                'iban'           => null,
                'email'          => 'procurement@nasa.gov',
            ]),
            'body' => (new InvoiceBodyDTOFactory())->make([
                'lines' => [
                    (new InvoiceLineDTOFactory())->make([
                        'id'          => Str::ulid()->toString(),
                        'description' => 'Custom cloud-based system for processing and analyzing satellite telemetry data',
                        'quantity'    => BigDecimal::of(1),
                        'unitPrice'   => BigDecimal::of($basePrice1),
                        'vatRate'     => new \App\Domain\Financial\DTOs\VatRateDTO(
                            id: Str::ulid()->toString(),
                            name: 'Tax Exempt',
                            rate: 0,
                            type: \App\Domain\Common\Enums\VatRateType::EXEMPT
                        ),
                        'totalNet'   => BigDecimal::of($basePrice1),
                        'totalVat'   => BigDecimal::of(0),
                        'totalGross' => BigDecimal::of($basePrice1),
                        'productId'  => null,
                    ]),
                    (new InvoiceLineDTOFactory())->make([
                        'id'          => Str::ulid()->toString(),
                        'description' => 'Real-time monitoring dashboard for mission control operations',
                        'quantity'    => BigDecimal::of(1),
                        'unitPrice'   => BigDecimal::of($basePrice2),
                        'vatRate'     => new \App\Domain\Financial\DTOs\VatRateDTO(
                            id: Str::ulid()->toString(),
                            name: 'Tax Exempt',
                            rate: 0,
                            type: \App\Domain\Common\Enums\VatRateType::EXEMPT
                        ),
                        'totalNet'   => BigDecimal::of($basePrice2),
                        'totalVat'   => BigDecimal::of(0),
                        'totalGross' => BigDecimal::of($basePrice2),
                        'productId'  => null,
                    ]),
                    (new InvoiceLineDTOFactory())->make([
                        'id'          => Str::ulid()->toString(),
                        'description' => '24/7 technical support and system maintenance',
                        'quantity'    => BigDecimal::of($quantity3),
                        'unitPrice'   => BigDecimal::of($basePrice3),
                        'vatRate'     => new \App\Domain\Financial\DTOs\VatRateDTO(
                            id: Str::ulid()->toString(),
                            name: 'Tax Exempt',
                            rate: 0,
                            type: \App\Domain\Common\Enums\VatRateType::EXEMPT
                        ),
                        'totalNet'   => BigDecimal::of($total3),
                        'totalVat'   => BigDecimal::of(0),
                        'totalGross' => BigDecimal::of($total3),
                        'productId'  => null,
                    ]),
                ],
                'exchange' => (new InvoiceExchangeDTOFactory())->make([
                    'currency'     => 'PLN',
                    'exchangeRate' => BigDecimal::of('1.0'),
                    'date'         => now()->toDateString(),
                ]),
                'description' => 'Payment terms: Net 30 days. All services delivered according to Contract #NASA-2024-SB-001.',
            ]),
            'payment' => (new InvoicePaymentDTOFactory())->make([
                'status'     => \App\Domain\Financial\Enums\PaymentStatus::PENDING,
                'dueDate'    => null, // Will be set by withDates method
                'paidDate'   => null,
                'paidAmount' => BigDecimal::of('0'),
                'method'     => \App\Domain\Financial\Enums\PaymentMethod::BANK_TRANSFER,
                'reference'  => 'NASA-Contract-2024-SB-001',
                'terms'      => 'Net 30',
                'notes'      => 'Government contract payment',
            ]),
            'options' => (new InvoiceOptionsDTOFactory())->make([
                'language'  => 'en',
                'template'  => 'government_contract',
                'sendEmail' => false,
                'emailTo'   => ['procurement@nasa.gov'],
            ]),
        ]);
    }
}
