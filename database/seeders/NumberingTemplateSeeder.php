<?php

namespace Database\Seeders;

use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Invoice\Enums\ResetPeriod;
use App\Domain\Invoice\Models\NumberingTemplate;
use App\Domain\Tenant\Models\Tenant;
use App\Helpers\Ulid;
use Illuminate\Database\Seeder;

class NumberingTemplateSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () {
            $templates = [
                [
                    'type'   => InvoiceType::Basic,
                    'format' => 'YYYY/NNNN',
                ],
                [
                    'type'   => InvoiceType::AdvancePayment,
                    'format' => 'ADV/YYYY/NNNN',
                ],
                [
                    'type'   => InvoiceType::Export,
                    'format' => 'EXP/YYYY/NNNN',
                ],
                [
                    'type'   => InvoiceType::Settlement,
                    'format' => 'SET/YYYY/NNNN',
                ],
                [
                    'type'   => InvoiceType::UE,
                    'format' => 'UE/YYYY/NNNN',
                ],
                [
                    'type'   => InvoiceType::DebitNote,
                    'format' => 'DEB/YYYY/NNNN',
                ],
                [
                    'type'   => InvoiceType::Import,
                    'format' => 'IMP/YYYY/NNNN',
                ],
            ];

            foreach ($templates as $template) {
                NumberingTemplate::create([
                    'id'           => Ulid::deterministic(['numbering-template', $template['type']->value]),
                    'tenant_id'    => Tenant::GLOBAL_TENANT_ID,
                    'invoice_type' => $template['type'],
                    'name'         => $template['type']->label(),
                    'format'       => $template['format'],
                    'next_number'  => 1,
                    'reset_period' => ResetPeriod::YEARLY,
                    'prefix'       => '',
                    'suffix'       => '',
                    'is_default'   => true,
                ]);

                NumberingTemplate::create([
                    'id'           => Ulid::deterministic(['numbering-template', $template['type']->getCorrectionType()->value]),
                    'tenant_id'    => Tenant::GLOBAL_TENANT_ID,
                    'invoice_type' => $template['type']->getCorrectionType(),
                    'name'         => $template['type']->getCorrectionType()->label(),
                    'format'       => $template['format'],
                    'next_number'  => 1,
                    'reset_period' => ResetPeriod::YEARLY,
                    'prefix'       => 'COR/',
                    'suffix'       => '',
                    'is_default'   => true,
                ]);
            }
        });
    }
}
