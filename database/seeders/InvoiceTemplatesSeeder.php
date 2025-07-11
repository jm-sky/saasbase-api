<?php

namespace Database\Seeders;

use App\Domain\Template\Enums\TemplateCategory;
use App\Domain\Template\Models\InvoiceTemplate;
use App\Domain\Tenant\Models\Tenant;
use App\Helpers\Ulid;
use Illuminate\Database\Seeder;

class InvoiceTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () {
            InvoiceTemplate::query()->delete();

            InvoiceTemplate::withoutGlobalScopes()->create([
                'id'           => Ulid::deterministic(['default']),
                'tenant_id'    => Tenant::GLOBAL_TENANT_ID,
                'name'         => 'default',
                'description'  => 'Clean, professional invoice template',
                'content'      => $this->loadTemplateFromFile('default'),
                'is_active'    => true,
                'is_default'   => true,
                'user_id'      => null,
                'category'     => TemplateCategory::INVOICE,
                'preview_data' => $this->getDefaultPreviewData(),
            ]);

            InvoiceTemplate::withoutGlobalScopes()->create([
                'id'           => Ulid::deterministic(['modern']),
                'tenant_id'    => Tenant::GLOBAL_TENANT_ID,
                'name'         => 'modern',
                'description'  => 'Modern design with accent colors',
                'content'      => $this->loadTemplateFromFile('modern'),
                'is_active'    => true,
                'is_default'   => false,
                'user_id'      => null,
                'category'     => TemplateCategory::INVOICE,
                'preview_data' => $this->getDefaultPreviewData(),
            ]);

            InvoiceTemplate::withoutGlobalScopes()->create([
                'id'           => Ulid::deterministic(['minimal']),
                'tenant_id'    => Tenant::GLOBAL_TENANT_ID,
                'name'         => 'minimal',
                'description'  => 'Clean, minimal design',
                'content'      => $this->loadTemplateFromFile('minimal'),
                'is_active'    => true,
                'is_default'   => false,
                'user_id'      => null,
                'category'     => TemplateCategory::INVOICE,
                'preview_data' => $this->getDefaultPreviewData(),
            ]);

            InvoiceTemplate::withoutGlobalScopes()->create([
                'id'           => Ulid::deterministic(['corporate']),
                'tenant_id'    => Tenant::GLOBAL_TENANT_ID,
                'name'         => 'corporate',
                'description'  => 'Professional corporate design',
                'content'      => $this->loadTemplateFromFile('corporate'),
                'is_active'    => true,
                'is_default'   => false,
                'user_id'      => null,
                'category'     => TemplateCategory::INVOICE,
                'preview_data' => $this->getDefaultPreviewData(),
            ]);
        });
    }

    /**
     * Load template content from file.
     */
    private function loadTemplateFromFile(string $templateName): string
    {
        $templatePath = resource_path("templates/invoice/{$templateName}.hbs");

        if (!file_exists($templatePath)) {
            throw new \Exception("Template file not found: {$templatePath}");
        }

        return file_get_contents($templatePath);
    }

    private function getDefaultPreviewData(): array
    {
        return [
            'invoice' => [
                'id'                  => 'preview-001',
                'number'              => 'INV-2024-001',
                'type'                => 'invoice',
                'status'              => 'issued',
                'formattedTotalNet'   => '1000.00 zł',
                'formattedTotalTax'   => '230.00 zł',
                'formattedTotalGross' => '1230.00 zł',
                'currency'            => 'PLN',
                'currencySymbol'      => 'zł',
                'issueDate'           => '2024-07-06',
                'dueDate'             => '2024-08-05',
                'seller'              => [
                    'name'    => 'Example Company Sp. z o.o.',
                    'address' => 'ul. Przykładowa 123, 00-001 Warszawa',
                    'country' => 'Polska',
                    'taxId'   => '1234567890',
                    'email'   => 'kontakt@example.com',
                    'logoUrl' => null,
                ],
                'buyer' => [
                    'name'    => 'Client Company Ltd.',
                    'address' => '456 Client Street, Warsaw',
                    'country' => 'Poland',
                    'taxId'   => '0987654321',
                    'email'   => 'client@example.com',
                ],
                'lines' => [
                    [
                        'id'                  => 'line-1',
                        'description'         => 'Web Development Services',
                        'formattedQuantity'   => '40.00',
                        'formattedUnitPrice'  => '20.00 zł',
                        'formattedTotalNet'   => '800.00 zł',
                        'formattedTotalVat'   => '184.00 zł',
                        'formattedTotalGross' => '984.00 zł',
                        'vatRateName'         => 'Standard VAT',
                        'vatRateValue'        => 23.0,
                    ],
                    [
                        'id'                  => 'line-2',
                        'description'         => 'Consulting Services',
                        'formattedQuantity'   => '10.00',
                        'formattedUnitPrice'  => '20.00 zł',
                        'formattedTotalNet'   => '200.00 zł',
                        'formattedTotalVat'   => '46.00 zł',
                        'formattedTotalGross' => '246.00 zł',
                        'vatRateName'         => 'Standard VAT',
                        'vatRateValue'        => 23.0,
                    ],
                ],
                'vatSummary' => [
                    [
                        'vatRateName'    => 'Standard VAT',
                        'vatRateValue'   => 23.0,
                        'formattedNet'   => '1000.00 zł',
                        'formattedVat'   => '230.00 zł',
                        'formattedGross' => '1230.00 zł',
                    ],
                ],
                'payment' => [
                    'status'      => 'pending',
                    'dueDate'     => '2024-08-05',
                    'method'      => 'bank_transfer',
                    'terms'       => 'Payment due within 30 days',
                    'bankAccount' => [
                        'iban'     => 'PL61109010140000071219812874',
                        'swift'    => 'WBKPPLPP',
                        'bankName' => 'Santander Bank Polska',
                    ],
                ],
            ],
        ];
    }
}
