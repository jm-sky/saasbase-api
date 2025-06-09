<?php

namespace Database\Factories\Domain\Invoice;

use App\Domain\Invoice\DTOs\InvoiceBuyerDTO;
use App\Domain\Invoice\DTOs\InvoiceDataDTO;
use App\Domain\Invoice\DTOs\InvoiceExchangeDTO;
use App\Domain\Invoice\DTOs\InvoiceLineDTO;
use App\Domain\Invoice\DTOs\InvoiceOptionsDTO;
use App\Domain\Invoice\DTOs\InvoicePaymentDTO;
use App\Domain\Invoice\DTOs\InvoiceSellerDTO;
use App\Domain\Invoice\Enums\InvoicePaymentMethod;
use App\Domain\Invoice\Enums\InvoicePaymentStatus;
use App\Domain\Invoice\Enums\InvoiceType;
use App\Domain\Invoice\Enums\VatRate;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Tenant\Models\Tenant;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
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
            'status'                => fake()->randomElement(['draft', 'sent', 'paid', 'cancelled']),
            'number'                => fake()->unique()->numerify('INV-####'),
            'numbering_template_id' => null, // Will be set when creating
            'total_net'             => $totalNet,
            'total_tax'             => $totalTax,
            'total_gross'           => $totalGross,
            'currency'              => fake()->currencyCode(),
            'exchange_rate'         => BigDecimal::of('1.0'),
            'issue_date'            => fake()->dateTimeBetween('-1 year', 'now'),
            'seller'                => InvoiceSellerDTOFactory::new()->make()->toArray(),
            'buyer'                 => InvoiceBuyerDTOFactory::new()->make()->toArray(),
            'data'                  => InvoiceDataDTOFactory::new()->make()->toArray(),
            'payment'               => InvoicePaymentDTOFactory::new()->make()->toArray(),
            'options'               => InvoiceOptionsDTOFactory::new()->make()->toArray(),
        ];
    }

    public function draft(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function sent(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
        ]);
    }

    public function paid(): self
    {
        return $this->state(fn (array $attributes) => [
            'status'  => 'paid',
            'payment' => InvoicePaymentDTOFactory::new()->paid()->make()->toArray(),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}

class InvoiceSellerDTOFactory extends Factory
{
    protected $model = InvoiceSellerDTO::class;

    public function definition(): array
    {
        return [
            'contractorId'   => fake()->numberBetween(1, 1000),
            'contractorType' => 'company',
            'name'           => fake()->company(),
            'taxId'          => fake()->numerify('##########'),
            'address'        => fake()->address(),
            'country'        => fake()->countryCode(),
            'iban'           => fake()->iban(),
            'email'          => fake()->companyEmail(),
        ];
    }
}

class InvoiceBuyerDTOFactory extends Factory
{
    protected $model = InvoiceBuyerDTO::class;

    public function definition(): array
    {
        return [
            'contractorId'   => fake()->numberBetween(1, 1000),
            'contractorType' => fake()->randomElement(['company', 'individual']),
            'name'           => fake()->company(),
            'taxId'          => fake()->numerify('##########'),
            'address'        => fake()->address(),
            'country'        => fake()->countryCode(),
            'iban'           => fake()->optional()->iban(),
            'email'          => fake()->email(),
        ];
    }
}

class InvoiceDataDTOFactory extends Factory
{
    protected $model = InvoiceDataDTO::class;

    public function definition(): array
    {
        $lines = InvoiceLineDTOFactory::new()->count(fake()->numberBetween(1, 5))->make();

        // Calculate VAT summary based on lines
        $vatSummary = [];

        foreach ($lines as $line) {
            $vatRate = $line->vatRate->value;

            if (!isset($vatSummary[$vatRate])) {
                $vatSummary[$vatRate] = [
                    'vatRate' => $vatRate,
                    'net'     => 0,
                    'vat'     => 0,
                    'gross'   => 0,
                ];
            }
            $vatSummary[$vatRate]['net'] += $line->totalNet->toFloat();
            $vatSummary[$vatRate]['vat'] += $line->totalVat->toFloat();
            $vatSummary[$vatRate]['gross'] += $line->totalGross->toFloat();
        }

        return [
            'lines'      => $lines->toArray(),
            'vatSummary' => array_values($vatSummary),
            'exchange'   => InvoiceExchangeDTOFactory::new()->make()->toArray(),
        ];
    }
}

class InvoiceLineDTOFactory extends Factory
{
    protected $model = InvoiceLineDTO::class;

    public function definition(): array
    {
        $quantity  = BigDecimal::of(fake()->randomFloat(2, 1, 10));
        $unitPrice = BigDecimal::of(fake()->randomFloat(2, 10, 100));
        $vatRate   = fake()->randomElement(VatRate::cases());

        $totalNet   = $quantity->multipliedBy($unitPrice);
        $totalVat   = $totalNet->multipliedBy($vatRate->value / 100);
        $totalGross = $totalNet->plus($totalVat);

        return [
            'id'          => Str::ulid()->toString(),
            'description' => fake()->sentence(),
            'quantity'    => $quantity,
            'unitPrice'   => $unitPrice,
            'vatRate'     => $vatRate,
            'totalNet'    => $totalNet,
            'totalVat'    => $totalVat,
            'totalGross'  => $totalGross,
            'productId'   => fake()->optional()->ulid(),
        ];
    }
}

class InvoicePaymentDTOFactory extends Factory
{
    protected $model = InvoicePaymentDTO::class;

    public function definition(): array
    {
        return [
            'status'     => InvoicePaymentStatus::PENDING,
            'dueDate'    => Carbon::now()->addDays(14),
            'paidDate'   => null,
            'paidAmount' => BigDecimal::of('0'),
            'method'     => fake()->randomElement(InvoicePaymentMethod::cases()),
            'reference'  => fake()->numerify('PAY-####'),
            'terms'      => 'Net 14',
            'notes'      => fake()->optional()->sentence(),
        ];
    }

    public function paid(): self
    {
        return $this->state(fn (array $attributes) => [
            'status'     => InvoicePaymentStatus::PAID,
            'paidDate'   => Carbon::now(),
            'paidAmount' => BigDecimal::of(fake()->randomFloat(2, 100, 1000)),
        ]);
    }
}

class InvoiceOptionsDTOFactory extends Factory
{
    protected $model = InvoiceOptionsDTO::class;

    public function definition(): array
    {
        return [
            'language'  => fake()->randomElement(['en', 'pl']),
            'template'  => 'default',
            'sendEmail' => fake()->boolean(),
            'emailTo'   => [fake()->email()],
        ];
    }
}

class InvoiceExchangeDTOFactory extends Factory
{
    protected $model = InvoiceExchangeDTO::class;

    public function definition(): array
    {
        return [
            'currency'     => fake()->currencyCode(),
            'exchangeRate' => BigDecimal::of(fake()->randomFloat(6, 0.5, 1.5)),
            'date'         => fake()->date(),
        ];
    }
}
