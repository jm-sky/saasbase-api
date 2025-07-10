<?php

namespace Database\Seeders;

use App\Domain\Template\Enums\TemplateCategory;
use App\Domain\Template\Models\InvoiceTemplate;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Seeder;

class InvoiceTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () {
            InvoiceTemplate::query()->delete();

            InvoiceTemplate::withoutGlobalScopes()->create([
                'tenant_id'    => Tenant::GLOBAL_TENANT_ID,
                'name'         => 'default',
                'description'  => 'Clean, professional invoice template',
                'content'      => $this->getDefaultTemplate(),
                'is_active'    => true,
                'is_default'   => true,
                'user_id'      => null,
                'category'     => TemplateCategory::INVOICE,
                'preview_data' => $this->getDefaultPreviewData(),
            ]);

            InvoiceTemplate::withoutGlobalScopes()->create([
                'tenant_id'    => Tenant::GLOBAL_TENANT_ID,
                'name'         => 'modern',
                'description'  => 'Modern design with accent colors',
                'content'      => $this->getModernTemplate(),
                'is_active'    => true,
                'is_default'   => false,
                'user_id'      => null,
                'category'     => TemplateCategory::INVOICE,
                'preview_data' => $this->getDefaultPreviewData(),
            ]);

            InvoiceTemplate::withoutGlobalScopes()->create([
                'tenant_id' => Tenant::GLOBAL_TENANT_ID,

                'name'         => 'minimal',
                'description'  => 'Clean, minimal design',
                'content'      => $this->getMinimalTemplate(),
                'is_active'    => true,
                'is_default'   => false,
                'user_id'      => null,
                'category'     => TemplateCategory::INVOICE,
                'preview_data' => $this->getDefaultPreviewData(),
            ]);

            InvoiceTemplate::withoutGlobalScopes()->create([
                'tenant_id' => Tenant::GLOBAL_TENANT_ID,

                'name'         => 'corporate',
                'description'  => 'Professional corporate design',
                'content'      => $this->getCorporateTemplate(),
                'is_active'    => true,
                'is_default'   => false,
                'user_id'      => null,
                'category'     => TemplateCategory::INVOICE,
                'preview_data' => $this->getDefaultPreviewData(),
            ]);
        });
    }

    private function getDefaultTemplate(): string
    {
        return '
<div class="invoice-container" style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, \'Noto Sans\', sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\', \'Noto Color Emoji\';">
    {{#if invoice.seller.logoUrl}}
    <div class="logo-header mb-4">
        <div class="flex justify-between items-center">
            <div>
                {{{logoUrl invoice.seller.logoUrl width="140px"}}}
            </div>
            <div class="text-right">
                <h1 class="text-2xl font-bold accent-text">{{t "invoices.invoice"}}</h1>
                <p class="secondary-text">{{invoice.number}}</p>
            </div>
        </div>
    </div>
    {{else}}
    <div class="invoice-header mb-4">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold accent-text">{{t "invoices.invoice"}}</h1>
                <p class="secondary-text">{{invoice.number}}</p>
            </div>
            <div class="text-right">
                {{#if invoice.issueDate}}
                <p class="text-sm secondary-text">{{t "invoices.issue_date"}} {{invoice.issueDate}}</p>
                {{/if}}
                {{#if invoice.dueDate}}
                <p class="text-sm secondary-text">{{t "invoices.due_date"}} {{invoice.dueDate}}</p>
                {{/if}}
            </div>
        </div>
    </div>
    {{/if}}

    <div class="invoice-parties mb-4">
        <div class="flex justify-between">
            <div class="w-1/2 pr-4">
                <h3 class="text-base font-semibold text-gray-900 mb-1">{{t "invoices.from"}}</h3>
                <div class="text-gray-600 text-sm">
                    {{#if invoice.seller.name}}
                    <p class="font-medium">{{invoice.seller.name}}</p>
                    {{/if}}
                    {{#if invoice.seller.address}}
                    <p>{{invoice.seller.address}}</p>
                    {{/if}}
                    {{#if invoice.seller.country}}
                    <p>{{invoice.seller.country}}</p>
                    {{/if}}
                    {{#if invoice.seller.taxId}}
                    <p>{{t "invoices.tax_id"}} {{invoice.seller.taxId}}</p>
                    {{/if}}
                    {{#if invoice.seller.email}}
                    <p>{{invoice.seller.email}}</p>
                    {{/if}}
                </div>
            </div>

            <div class="w-1/2 pl-4">
                <h3 class="text-base font-semibold text-gray-900 mb-1">{{t "invoices.to"}}</h3>
                <div class="text-gray-600 text-sm">
                    {{#if invoice.buyer.name}}
                    <p class="font-medium">{{invoice.buyer.name}}</p>
                    {{/if}}
                    {{#if invoice.buyer.address}}
                    <p>{{invoice.buyer.address}}</p>
                    {{/if}}
                    {{#if invoice.buyer.country}}
                    <p>{{invoice.buyer.country}}</p>
                    {{/if}}
                    {{#if invoice.buyer.taxId}}
                    <p>{{t "invoices.tax_id"}} {{invoice.buyer.taxId}}</p>
                    {{/if}}
                    {{#if invoice.buyer.email}}
                    <p>{{invoice.buyer.email}}</p>
                    {{/if}}
                </div>
            </div>
        </div>
    </div>

    {{#if invoice.description}}
    <div class="invoice-description mb-4">
        <h3 class="text-base font-semibold text-gray-900 mb-1">{{t "invoices.description"}}</h3>
        <p class="text-gray-600 text-sm">{{invoice.description}}</p>
    </div>
    {{/if}}

    <div class="invoice-items mb-4">
        <table class="invoice-table w-full">
            <thead>
                <tr class="accent-bg text-white">
                    <th class="text-left py-2 px-2 text-sm">{{t "invoices.description"}}</th>
                    <th class="text-center py-2 px-2 text-sm">{{t "invoices.quantity"}}</th>
                    <th class="text-right py-2 px-2 text-sm">{{t "invoices.unit_price"}}</th>
                    <th class="text-right py-2 px-2 text-sm">{{t "invoices.total_net"}}</th>
                    <th class="text-right py-2 px-2 text-sm">{{t "invoices.vat"}}</th>
                    <th class="text-right py-2 px-2 text-sm">{{t "invoices.total_gross"}}</th>
                </tr>
            </thead>
            <tbody>
                {{#each invoice.lines}}
                <tr>
                    <td class="py-2 px-2 text-sm">
                        {{#if description}}
                        <div class="font-medium">{{description}}</div>
                        {{/if}}
                        <div class="text-xs text-gray-600">{{vatRateName}} ({{vatRateValue}}%)</div>
                    </td>
                    <td class="text-center py-2 px-2 text-sm">{{formattedQuantity}}</td>
                    <td class="text-right py-2 px-2 text-sm">{{formattedUnitPrice}}</td>
                    <td class="text-right py-2 px-2 text-sm">{{formattedTotalNet}}</td>
                    <td class="text-right py-2 px-2 text-sm">{{formattedTotalVat}}</td>
                    <td class="text-right py-2 px-2 text-sm font-semibold">{{formattedTotalGross}}</td>
                </tr>
                {{/each}}
            </tbody>
        </table>
    </div>

    {{#if invoice.vatSummary}}
    <div class="vat-summary mb-4">
        <h3 class="text-base font-semibold text-gray-900 mb-2">{{t "invoices.vat_summary"}}</h3>
        <table class="w-full border border-gray-300">
            <thead>
                <tr class="bg-gray-50">
                    <th class="text-left py-1 px-2 border-b text-sm">{{t "invoices.vat_rate"}}</th>
                    <th class="text-right py-1 px-2 border-b text-sm">{{t "invoices.net_amount"}}</th>
                    <th class="text-right py-1 px-2 border-b text-sm">{{t "invoices.vat_amount"}}</th>
                    <th class="text-right py-1 px-2 border-b text-sm">{{t "invoices.gross_amount"}}</th>
                </tr>
            </thead>
            <tbody>
                {{#each invoice.vatSummary}}
                <tr>
                    <td class="py-1 px-2 border-b text-sm">{{vatRateName}} ({{vatRateValue}}%)</td>
                    <td class="text-right py-1 px-2 border-b text-sm">{{formattedNet}}</td>
                    <td class="text-right py-1 px-2 border-b text-sm">{{formattedVat}}</td>
                    <td class="text-right py-1 px-2 border-b text-sm font-semibold">{{formattedGross}}</td>
                </tr>
                {{/each}}
            </tbody>
        </table>
    </div>
    {{/if}}

    <div class="invoice-totals mb-4">
        <div class="w-1/2 ml-auto">
            <div class="flex justify-between py-1 border-b border-gray-300 text-sm">
                <span class="text-gray-600">{{t "invoices.subtotal"}}</span>
                <span class="font-medium">{{invoice.formattedTotalNet}}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-gray-300 text-sm">
                <span class="text-gray-600">{{t "invoices.vat"}}:</span>
                <span class="font-medium">{{invoice.formattedTotalTax}}</span>
            </div>
            <div class="flex justify-between py-2 text-lg font-bold accent-text">
                <span>{{t "invoices.total"}}</span>
                <span>{{invoice.formattedTotalGross}}</span>
            </div>
        </div>
    </div>

    {{#if invoice.exchange}}
    <div class="exchange-info mb-4 bg-gray-50 p-2">
        <h3 class="text-base font-semibold text-gray-900 mb-1">{{t "invoices.currency"}} {{invoice.exchange.currency}}</h3>
        {{#if invoice.exchange.formattedExchangeRate}}
        <p class="text-gray-600 text-sm">{{t "invoices.exchange_rate"}} {{invoice.exchange.formattedExchangeRate}}</p>
        {{/if}}
        {{#if invoice.exchange.date}}
        <p class="text-gray-600 text-sm">{{t "invoices.date"}} {{invoice.exchange.date}}</p>
        {{/if}}
    </div>
    {{/if}}

    {{#if invoice.payment}}
    <div class="payment-info mb-4">
        <h3 class="text-base font-semibold text-gray-900 mb-2">{{t "invoices.payment_details"}}</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p><strong>{{t "invoices.payment_method"}}</strong> {{invoice.payment.method}}</p>
                <p><strong>{{t "invoices.payment_status"}}</strong> {{invoice.payment.status}}</p>
                {{#if invoice.payment.reference}}
                <p><strong>{{t "invoices.reference"}}</strong> {{invoice.payment.reference}}</p>
                {{/if}}
            </div>
            <div>
                {{#if invoice.payment.formattedPaidAmount}}
                <p><strong>{{t "invoices.paid_amount"}}</strong> {{invoice.payment.formattedPaidAmount}}</p>
                {{/if}}
                {{#if invoice.payment.paidDate}}
                <p><strong>{{t "invoices.paid_date"}}</strong> {{invoice.payment.paidDate}}</p>
                {{/if}}
            </div>
        </div>

        {{#if invoice.payment.bankAccount}}
        <div class="bank-details mt-2 bg-gray-50 p-2">
            <h4 class="font-semibold mb-1 text-sm">{{t "invoices.bank_account"}}</h4>
            {{#if invoice.payment.bankAccount.bankName}}
            <p class="text-sm"><strong>{{t "invoices.bank_name"}}</strong> {{invoice.payment.bankAccount.bankName}}</p>
            {{/if}}
            {{#if invoice.payment.bankAccount.iban}}
            <p class="text-sm"><strong>{{t "invoices.iban"}}</strong> {{invoice.payment.bankAccount.iban}}</p>
            {{/if}}
            {{#if invoice.payment.bankAccount.swift}}
            <p class="text-sm"><strong>{{t "invoices.swift"}}</strong> {{invoice.payment.bankAccount.swift}}</p>
            {{/if}}
        </div>
        {{/if}}

        {{#if invoice.payment.terms}}
        <div class="payment-terms mt-2">
            <h4 class="font-semibold mb-1 text-sm">{{t "invoices.payment_terms"}}</h4>
            <p class="text-gray-600 text-sm">{{invoice.payment.terms}}</p>
        </div>
        {{/if}}

        {{#if invoice.payment.notes}}
        <div class="payment-notes mt-2">
            <h4 class="font-semibold mb-1 text-sm">{{t "invoices.notes"}}</h4>
            <p class="text-gray-600 text-sm">{{invoice.payment.notes}}</p>
        </div>
        {{/if}}
    </div>
    {{/if}}

    {{#if options.includeSignatures}}
    <div class="signatures-section mt-4">
        {{#if options.issuerSignature}}
        <div class="signature-block w-1/2 float-left">
            <h4 class="font-semibold mb-1 text-sm">{{t "invoices.authorized_by"}}</h4>
            <div class="signature-line mb-1" style="border-bottom: 1px solid #000; height: 30px; position: relative;">
                {{#if options.issuerSignature.imageUrl}}
                {{{signatureUrl options.issuerSignature.imageUrl}}}
                {{/if}}
            </div>
            <div class="signature-name font-medium text-sm">{{options.issuerSignature.name}}</div>
            {{#if options.issuerSignature.title}}
            <div class="signature-title text-xs text-gray-600">{{options.issuerSignature.title}}</div>
            {{/if}}
            {{#if options.issuerSignature.date}}
            <div class="signature-date text-xs text-gray-600">{{options.issuerSignature.date}}</div>
            {{/if}}
        </div>
        {{/if}}

        {{#if options.receiverSignature}}
        <div class="signature-block w-1/2 float-right">
            <h4 class="font-semibold mb-1 text-sm">{{t "invoices.received_by"}}</h4>
            <div class="signature-line mb-1" style="border-bottom: 1px solid #000; height: 30px; position: relative;">
                {{#if options.receiverSignature.imageUrl}}
                {{{signatureUrl options.receiverSignature.imageUrl}}}
                {{/if}}
            </div>
            <div class="signature-name font-medium text-sm">{{options.receiverSignature.name}}</div>
            {{#if options.receiverSignature.title}}
            <div class="signature-title text-xs text-gray-600">{{options.receiverSignature.title}}</div>
            {{/if}}
            {{#if options.receiverSignature.date}}
            <div class="signature-date text-xs text-gray-600">{{options.receiverSignature.date}}</div>
            {{/if}}
        </div>
        {{/if}}
        <div style="clear: both;"></div>
    </div>
    {{/if}}

    {{#if invoice.seller.logoUrl}}
    <div class="logo-footer text-center mt-4 pt-2 border-t border-gray-200">
        {{{logoUrl invoice.seller.logoUrl width="100px"}}}
    </div>
    {{/if}}
</div>
';
    }

    private function getModernTemplate(): string
    {
        return str_replace(
            'accent-text">{{t "invoices.invoice"}}</h1>',
            'text-white">{{t "invoices.invoice"}}</h1>',
            str_replace(
                '<div class="invoice-header mb-8">',
                '<div class="invoice-header mb-8 accent-bg text-white p-6 -mx-6">',
                $this->getDefaultTemplate()
            )
        );
    }

    private function getMinimalTemplate(): string
    {
        return str_replace(
            ['text-3xl font-bold accent-text', 'text-lg font-semibold text-gray-900'],
            ['text-2xl font-light text-gray-900', 'text-base font-medium text-gray-900'],
            $this->getDefaultTemplate()
        );
    }

    private function getCorporateTemplate(): string
    {
        return str_replace(
            ['accent-bg text-white', 'text-lg font-semibold text-gray-900'],
            ['bg-gray-800 text-white', 'text-lg font-bold text-gray-900 border-b border-gray-300 pb-1'],
            $this->getDefaultTemplate()
        );
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
