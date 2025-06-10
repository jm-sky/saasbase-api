<?php

namespace App\Services\Signatures\Commands;

use App\Services\Signatures\XmlSignatureVerifierService;
use Illuminate\Console\Command;

class VerifyXmlSignatureCommand extends Command
{
    protected $signature = 'signatures:verify-xml {file : Path to the XML file}';

    protected $description = 'Verify XML signature using XmlSignatureVerifierService';

    public function handle(XmlSignatureVerifierService $service)
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error('File does not exist: ' . $filePath);

            return 1;
        }

        try {
            $xmlContent = file_get_contents($filePath);
            $result     = $service->verify($xmlContent);
            $this->info(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return 0;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());

            return 1;
        }
    }
}
