<?php

namespace App\Domain\Financial\Enums;

use App\Traits\HasEnumValues;

enum InvoiceType: string
{
    use HasEnumValues;

    case Basic                    = 'basic'; // Faktura podstawowa
    case BasicCorrection          = 'basic-correction'; // Korekta
    case AdvancePayment           = 'advance-payment'; // Faktura zaliczka
    case AdvancePaymentCorrection = 'advance-payment-correction'; // Faktura zaliczka korekta
    case Export                   = 'export'; // Faktura eksport towarów
    case ExportCorrection         = 'export-correction'; // Faktura eksport towarów korekta
    case Settlement               = 'settlement'; // Faktura końcowa do zaliczki
    case SettlementCorrection     = 'settlement-correction'; // Faktura końcowa do zaliczki korekta
    case Proforma                 = 'proforma'; // Proforma
    case UE                       = 'ue'; // Faktura sprzedaży UE
    case UECorrection             = 'ue-correction'; // Faktura sprzedaży UE korekta
    case DebitNote                = 'debit-note'; // Nota obciążeniowa
    case DebitNoteCorrection      = 'debit-note-correction'; // Nota obciążeniowa korekta
    case Import                   = 'import'; // Faktura import towarów
    case ImportCorrection         = 'import-correction'; // Faktura import towarów korekta

    public function label(): string
    {
        return match ($this) {
            self::Basic                    => 'Faktura podstawowa',
            self::BasicCorrection          => 'Korekta',
            self::AdvancePayment           => 'Faktura zaliczka',
            self::AdvancePaymentCorrection => 'Faktura zaliczka korekta',
            self::Export                   => 'Faktura eksport towarów',
            self::ExportCorrection         => 'Faktura eksport towarów korekta',
            self::Settlement               => 'Faktura końcowa do zaliczki',
            self::SettlementCorrection     => 'Faktura końcowa do zaliczki korekta',
            self::Proforma                 => 'Proforma',
            self::UE                       => 'Faktura sprzedaży UE',
            self::UECorrection             => 'Faktura sprzedaży UE korekta',
            self::DebitNote                => 'Nota obciążeniowa',
            self::DebitNoteCorrection      => 'Nota obciążeniowa korekta',
            self::Import                   => 'Faktura import towarów',
            self::ImportCorrection         => 'Faktura import towarów korekta',
        };
    }

    public function isCorrection(): bool
    {
        return str_ends_with($this->value, '-correction');
    }

    public function getBaseType(): self
    {
        if (!$this->isCorrection()) {
            return $this;
        }

        return match ($this) {
            self::BasicCorrection          => self::Basic,
            self::AdvancePaymentCorrection => self::AdvancePayment,
            self::ExportCorrection         => self::Export,
            self::SettlementCorrection     => self::Settlement,
            self::UECorrection             => self::UE,
            self::DebitNoteCorrection      => self::DebitNote,
            self::ImportCorrection         => self::Import,
            default                        => $this,
        };
    }

    public function getCorrectionType(): self
    {
        if ($this->isCorrection()) {
            return $this;
        }

        return match ($this) {
            self::Basic          => self::BasicCorrection,
            self::AdvancePayment => self::AdvancePaymentCorrection,
            self::Export         => self::ExportCorrection,
            self::Settlement     => self::SettlementCorrection,
            self::UE             => self::UECorrection,
            self::DebitNote      => self::DebitNoteCorrection,
            self::Import         => self::ImportCorrection,
            default              => $this,
        };
    }
}
