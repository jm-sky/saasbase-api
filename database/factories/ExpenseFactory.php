<?php

namespace Database\Factories;

use App\Domain\Expense\Models\Expense;
use App\Domain\Financial\DTOs\InvoicePaymentBankAccountDTO;
use App\Domain\Financial\DTOs\PaymentMethodDTO;
use App\Domain\Financial\Enums\AllocationStatus;
use App\Domain\Financial\Enums\ApprovalStatus;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Financial\Enums\PaymentStatus;
use App\Domain\Financial\Enums\VatRateType;
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
            'status'            => fake()->randomElement([
                ...InvoiceStatus::cases(),
                InvoiceStatus::ISSUED,
                InvoiceStatus::COMPLETED,
                InvoiceStatus::ISSUED,
                InvoiceStatus::ISSUED,
                InvoiceStatus::COMPLETED,
                InvoiceStatus::COMPLETED,
            ]),
            'ocr_status'        => null,
            'allocation_status' => AllocationStatus::PENDING,
            'approval_status'   => ApprovalStatus::NOT_REQUIRED,
            'delivery_status'   => null,
            'payment_status'    => PaymentStatus::PAID,
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
            'status' => InvoiceStatus::DRAFT->value,
        ]);
    }

    public function sent(): self
    {
        return $this->state(fn (array $attributes) => [
            'status'          => InvoiceStatus::ISSUED->value,
            'delivery_status' => \App\Domain\Financial\Enums\DeliveryStatus::SENT->value,
        ]);
    }

    public function paid(): self
    {
        return $this->state(fn (array $attributes) => [
            'status'         => InvoiceStatus::COMPLETED->value,
            'payment_status' => PaymentStatus::PAID->value,
            'payment'        => (new InvoicePaymentDTOFactory())->paid(),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::CANCELLED->value,
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
        $basePrice1 = fake()->numberBetween(100, 500);
        $quantity1  = fake()->numberBetween(2, 4);
        $totalNet   = $basePrice1 * $quantity1;
        $totalVat   = $totalNet * 0.23;
        $totalGross = $totalNet + $totalVat;

        return $this->state(fn (array $attributes) => [
            'currency'          => 'PLN',
            'total_net'         => $totalNet,
            'total_tax'         => $totalVat,
            'total_gross'       => $totalGross,
            'seller'            => (new InvoicePartyDTOFactory())->make([
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
                            type: VatRateType::PERCENTAGE
                        ),
                        'totalNet'   => BigDecimal::of($totalNet),
                        'totalVat'   => BigDecimal::of($totalVat),
                        'totalGross' => BigDecimal::of($totalGross),
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
                'status'     => PaymentStatus::PENDING,
                'dueDate'    => null, // Will be set by withDates method
                'paidDate'   => null,
                'paidAmount' => BigDecimal::of('0'),
                'method'     => PaymentMethodDTO::default(),
                'reference'  => 'BP-EXP-2024-001',
                'terms'      => 'Net 30',
                'notes'      => 'Expense from BP services',
            ]),
        ]);
    }

    public function receivedFromOvh(Tenant $tenant): self
    {
        // Constant server cost
        $serverCost = 500;

        // Random storage cost between 5-200 PLN
        $storageCost = fake()->numberBetween(5, 200);

        $totalNet   = $serverCost + $storageCost;
        $vatRate    = 0.23;
        $totalVat   = $totalNet * $vatRate;
        $totalGross = $totalNet + $totalVat;

        return $this->state(fn (array $attributes) => [
            'currency'          => 'PLN',
            'total_net'         => $totalNet,
            'total_tax'         => $totalVat,
            'total_gross'       => $totalGross,
            'seller'            => (new InvoicePartyDTOFactory())->make([
                'contractorType' => 'company',
                'name'           => 'OVH Sp. z o.o.',
                'address'        => 'Powstańców Śląskich 9, 53-332 Wrocław',
                'country'        => 'PL',
                'contractorId'   => null,
                'taxId'          => 'PL7010439804',
                'iban'           => 'PL75114011400000215160001002',
                'email'          => 'billing@ovh.pl',
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
                        'description' => 'Monthly dedicated server hosting',
                        'quantity'    => BigDecimal::of(1),
                        'unitPrice'   => BigDecimal::of($serverCost),
                        'vatRate'     => new \App\Domain\Financial\DTOs\VatRateDTO(
                            id: Str::ulid()->toString(),
                            name: '23% VAT',
                            rate: 23,
                            type: VatRateType::PERCENTAGE
                        ),
                        'totalNet'   => BigDecimal::of($serverCost),
                        'totalVat'   => BigDecimal::of($serverCost * $vatRate),
                        'totalGross' => BigDecimal::of($serverCost * (1 + $vatRate)),
                        'productId'  => null,
                    ]),
                    (new InvoiceLineDTOFactory())->make([
                        'id'          => Str::ulid()->toString(),
                        'description' => 'Additional storage services',
                        'quantity'    => BigDecimal::of(1),
                        'unitPrice'   => BigDecimal::of($storageCost),
                        'vatRate'     => new \App\Domain\Financial\DTOs\VatRateDTO(
                            id: Str::ulid()->toString(),
                            name: '23% VAT',
                            rate: 23,
                            type: VatRateType::PERCENTAGE
                        ),
                        'totalNet'   => BigDecimal::of($storageCost),
                        'totalVat'   => BigDecimal::of($storageCost * $vatRate),
                        'totalGross' => BigDecimal::of($storageCost * (1 + $vatRate)),
                        'productId'  => null,
                    ]),
                ],
                'exchange' => (new InvoiceExchangeDTOFactory())->make([
                    'currency'     => 'PLN',
                    'exchangeRate' => BigDecimal::of('1.0'),
                    'date'         => now()->toDateString(),
                ]),
                'description' => 'Services provided under Contract #OVH-2024-SB-001. Payment due within 30 days.',
            ]),
            'payment' => (new InvoicePaymentDTOFactory())->make([
                'status'      => PaymentStatus::PENDING,
                'dueDate'     => null, // Will be set by withDates method
                'paidDate'    => null,
                'paidAmount'  => BigDecimal::of('0'),
                'method'      => PaymentMethodDTO::default(),
                'reference'   => 'OVH-EXP-2024-001',
                'terms'       => 'Net 30',
                'notes'       => 'Expense from OVH services',
                'bankAccount' => new InvoicePaymentBankAccountDTO(
                    iban: 'PL75114011400000215160001002',
                    country: 'PL',
                    swift: 'BREXPLPWROC',
                    bankName: 'mBank S.A.',
                ),
            ]),
        ]);
    }
}
