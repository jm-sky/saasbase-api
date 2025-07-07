<?php

namespace App\Domain\Template\Enums;

enum TemplateCategory: string
{
    case INVOICE     = 'invoice';
    case QUOTE       = 'quote';
    case RECEIPT     = 'receipt';
    case ESTIMATE    = 'estimate';
    case CREDIT_NOTE = 'credit_note';

    public function label(): string
    {
        return match ($this) {
            self::INVOICE     => __('templates.categories.invoice'),
            self::QUOTE       => __('templates.categories.quote'),
            self::RECEIPT     => __('templates.categories.receipt'),
            self::ESTIMATE    => __('templates.categories.estimate'),
            self::CREDIT_NOTE => __('templates.categories.credit_note'),
        };
    }
}
