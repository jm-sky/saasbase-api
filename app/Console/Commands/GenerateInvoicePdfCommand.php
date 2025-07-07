<?php

namespace App\Console\Commands;

use App\Domain\Invoice\Models\Invoice;
use App\Domain\Template\Services\InvoiceGeneratorService;
use Illuminate\Console\Command;

class GenerateInvoicePdfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:generate-pdf
                           {invoice_id : The ID of the invoice to generate PDF for}
                           {--template_id= : Optional template ID to use}
                           {--collection=invoices : Media collection name to store the PDF}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate PDF for an invoice and attach it to the invoice model';

    /**
     * Execute the console command.
     */
    public function handle(InvoiceGeneratorService $invoiceGenerator): int
    {
        $invoiceId  = $this->argument('invoice_id');
        $templateId = $this->option('template_id');
        $collection = $this->option('collection');

        $this->info("Generating PDF for invoice: {$invoiceId}");

        try {
            // Find the invoice
            $invoice = Invoice::find($invoiceId);

            if (!$invoice) {
                $this->error("Invoice with ID {$invoiceId} not found.");

                return self::FAILURE;
            }

            $this->info("Found invoice: {$invoice->number}");
            $this->info("Tenant: {$invoice->tenant_id}");

            // Generate and attach PDF
            $media = $invoiceGenerator->generateAndAttachPdf(
                $invoice,
                $templateId,
                $collection
            );

            $this->info('✅ PDF generated successfully!');
            $this->info("Media ID: {$media->id}");
            $this->info("File name: {$media->file_name}");
            $this->info('File size: ' . $this->formatBytes($media->size));
            $this->info("Collection: {$media->collection_name}");
            $this->info("URL: {$media->getUrl()}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to generate PDF: {$e->getMessage()}");
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; ++$i) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }
}
