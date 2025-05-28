<?php

namespace App\Services\RegonLookup;

use App\Services\RegonLookup\Enums\EntityType;
use App\Services\RegonLookup\Enums\RegonReportName;

class RegonReportResolver
{
    public static function resolveReportName(EntityType $entityType): RegonReportName
    {
        return match ($entityType) {
            EntityType::LegalPerson   => RegonReportName::BIR11OsPrawna,
            EntityType::NaturalPerson => RegonReportName::BIR11OsFizyczna,
            EntityType::LocalUnit     => RegonReportName::BIR11JednLokalna,
        };
    }
}
