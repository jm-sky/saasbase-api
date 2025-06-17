<?php

namespace Database\Factories\DTOs;

use App\Domain\Financial\DTOs\InvoiceBodyDTO;
use App\Domain\Financial\DTOs\VatRateDTO;

class InvoiceBodyDTOFactory extends DTOFactory
{
    public function make(): InvoiceBodyDTO
    {
        $lines = [];
        $count = fake()->numberBetween(1, 5);

        for ($i = 0; $i < $count; ++$i) {
            $lines[] = (new InvoiceLineDTOFactory())->make();
        }

        // Calculate VAT summary based on lines
        $vatSummary = [];

        foreach ($lines as $line) {
            /** @var VatRateDTO $vatRate */
            $vatRate = $line->vatRate;

            if (!isset($vatSummary[$vatRate->name])) {
                $vatSummary[$vatRate->name] = [
                    'vatRate' => $vatRate,
                    'net'     => 0,
                    'vat'     => 0,
                    'gross'   => 0,
                ];
            }
            $vatSummary[$vatRate->name]['net'] += $line->totalNet->toFloat();
            $vatSummary[$vatRate->name]['vat'] += $line->totalVat->toFloat();
            $vatSummary[$vatRate->name]['gross'] += $line->totalGross->toFloat();
        }

        $vatSummaryDTOs = [];

        foreach ($vatSummary as $summary) {
            $vatSummaryDTOs[] = new \App\Domain\Financial\DTOs\InvoiceVatSummaryDTO(
                $summary['vatRate'],
                \Brick\Math\BigDecimal::of($summary['net']),
                \Brick\Math\BigDecimal::of($summary['vat']),
                \Brick\Math\BigDecimal::of($summary['gross'])
            );
        }

        return new InvoiceBodyDTO(
            lines: $lines,
            vatSummary: $vatSummaryDTOs,
            exchange: (new InvoiceExchangeDTOFactory())->make(),
            description: fake()->sentence(),
        );
    }
}
