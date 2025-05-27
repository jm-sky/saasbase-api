<?php

namespace App\Services\GusLookup;

use App\Services\GusLookup\Enums\EntityType;
use App\Services\GusLookup\Enums\GusReportName;

class GusReportResolver
{
    public static function resolve(array $basicRecord): ?GusReportName
    {
        $type = EntityType::tryFrom($basicRecord['type'] ?? '');

        return match ($type) {
            EntityType::P  => GusReportName::BIR11OsPrawna,
            EntityType::LP => GusReportName::BIR11JednLokalnaOsPrawnej,
            EntityType::LF => GusReportName::BIR11JednLokalnaOsFizycznej,

            EntityType::F  => self::resolveForFizyczna($basicRecord),

            default => null,
        };
    }

    protected static function resolveForFizyczna(array $record): GusReportName
    {
        $silosId = $record['silosId'] ?? null;

        return match ($silosId) {
            '6' => GusReportName::BIR11OsFizycznaRolnicza,
            '4' => GusReportName::BIR11OsFizycznaDzialalnoscCeidg,
            default => GusReportName::BIR11OsFizycznaDzialalnoscPozostala,
        };
    }
}