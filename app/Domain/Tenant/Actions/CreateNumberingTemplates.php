<?php

namespace App\Domain\Tenant\Actions;

use App\Domain\Financial\Enums\InvoiceType;
use App\Domain\Invoice\Enums\ResetPeriod;
use App\Domain\Invoice\Models\NumberingTemplate;
use App\Domain\Tenant\Models\Tenant;
use App\Helpers\Ulid;

class CreateNumberingTemplates
{
    protected array $templates = [
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

    public function execute(Tenant $tenant): void
    {
        foreach ($this->templates as $template) {
            NumberingTemplate::create([
                'id'           => Ulid::deterministic(['numbering-template', $tenant->id, $template['type']->value]),
                'tenant_id'    => $tenant->id,
                'invoice_type' => $template['type'],
                'name'         => $template['type']->label(),
                'format'       => $template['format'],
                'next_number'  => 1,
                'reset_period' => ResetPeriod::YEARLY,
                'prefix'       => '',
                'suffix'       => '',
                'is_default'   => true,
            ]);

            $correctionType = $template['type']->getCorrectionType();

            NumberingTemplate::create([
                'id'           => Ulid::deterministic(['numbering-template', $tenant->id, $correctionType->value]),
                'tenant_id'    => $tenant->id,
                'invoice_type' => $correctionType,
                'name'         => $correctionType->label(),
                'format'       => $template['format'],
                'next_number'  => 1,
                'reset_period' => ResetPeriod::YEARLY,
                'prefix'       => 'COR/',
                'suffix'       => '',
                'is_default'   => true,
            ]);
        }
    }
}
