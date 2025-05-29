<?php

namespace App\Services\RegonLookup\Exceptions;

use App\Services\RegonLookup\Enums\RegonReportName;

class ReportNotImplementedException extends RegonLookupException
{
    public static function forReport(RegonReportName $reportName): self
    {
        return new self("Report {$reportName->value} is not implemented");
    }
}
