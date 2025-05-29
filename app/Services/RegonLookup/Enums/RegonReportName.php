<?php

namespace App\Services\RegonLookup\Enums;

enum RegonReportName: string
{
    // Osoby prawne
    case BIR11LegalPerson = 'BIR11OsPrawna';

    // Osoby fizyczne prowadzące działalność
    case BIR11NaturalPersonCeidg                = 'BIR11OsFizycznaDzialalnoscCeidg';
    case BIR11NaturalPersonDzialalnoscPozostala = 'BIR11OsFizycznaDzialalnoscPozostala';
    case BIR11NaturalPersonRolnicza             = 'BIR11OsFizycznaRolnicza';

    // Jednostki lokalne
    case BIR11LocalLegalPersonUnit   = 'BIR11JednLokalnaOsPrawnej';
    case BIR11LocalNaturalPersonUnit = 'BIR11JednLokalnaOsFizycznej';
}
