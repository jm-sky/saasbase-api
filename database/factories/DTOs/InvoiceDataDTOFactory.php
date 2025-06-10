<?php

namespace Database\Factories\DTOs;

use App\Domain\Invoice\DTOs\InvoiceDataDTO;

class InvoiceDataDTOFactory extends DTOFactory
{
    public function make(): InvoiceDataDTO
    {
        $lines = [];
        $count = fake()->numberBetween(1, 5);

        for ($i = 0; $i < $count; ++$i) {
            $lines[] = (new InvoiceLineDTOFactory())->make();
        }

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

        $vatSummaryDTOs = [];

        foreach ($vatSummary as $summary) {
            $vatSummaryDTOs[] = new \App\Domain\Invoice\DTOs\InvoiceVatSummaryDTO(
                \App\Domain\Invoice\Enums\VatRate::from($summary['vatRate']),
                \Brick\Math\BigDecimal::of($summary['net']),
                \Brick\Math\BigDecimal::of($summary['vat']),
                \Brick\Math\BigDecimal::of($summary['gross'])
            );
        }

        return new InvoiceDataDTO(
            lines: $lines,
            vatSummary: $vatSummaryDTOs,
            exchange: (new InvoiceExchangeDTOFactory())->make(),
        );
    }
}
