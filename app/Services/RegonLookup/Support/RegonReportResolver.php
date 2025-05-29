<?php

namespace App\Services\RegonLookup\Support;

use App\Services\RegonLookup\Enums\EntityType;
use App\Services\RegonLookup\Enums\RegonReportName;

class RegonReportResolver
{
    public static function resolveReportName(EntityType $entityType): RegonReportName
    {
        return match ($entityType) {
            EntityType::LegalPerson             => RegonReportName::BIR11LegalPerson,
            EntityType::NaturalPerson           => RegonReportName::BIR11NaturalPersonCeidg,
            EntityType::LocalLegalPersonUnit    => RegonReportName::BIR11LocalLegalPersonUnit,
            EntityType::LocalNaturalPersonUnit  => RegonReportName::BIR11LocalNaturalPersonUnit,
        };
    }
}
