<?php

namespace App\Services\GusLookup\Enums;

enum GusReportName: string
{
    // Osoby prawne
    case BIR11OsPrawna = 'BIR11OsPrawna';

    // Osoby fizyczne prowadzące działalność
    case BIR11OsFizycznaDzialalnoscCeidg     = 'BIR11OsFizycznaDzialalnoscCeidg';
    case BIR11OsFizycznaDzialalnoscPozostala = 'BIR11OsFizycznaDzialalnoscPozostala';
    case BIR11OsFizycznaRolnicza             = 'BIR11OsFizycznaRolnicza';

    // Jednostki lokalne
    case BIR11JednLokalnaOsPrawnej   = 'BIR11JednLokalnaOsPrawnej';
    case BIR11JednLokalnaOsFizycznej = 'BIR11JednLokalnaOsFizycznej';

    // Dobór raportu do typu jednostki
    public static function getReportForEntity(EntityType $type): ?self
    {
        return match ($type) {
            EntityType::P  => self::BIR11OsPrawna,
            EntityType::F  => self::BIR11OsFizycznaDzialalnoscCeidg, // lub dynamiczne rozpoznanie CEIDG/rolnicza/inna
            EntityType::LP => self::BIR11JednLokalnaOsPrawnej,
            EntityType::LF => self::BIR11JednLokalnaOsFizycznej,
        };
    }
}
