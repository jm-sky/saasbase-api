<?php

namespace App\Services\RegonLookup\Enums;

enum EntityType: string
{
    case LegalPerson            = 'P';  // Osoba prawna
    case NaturalPerson          = 'F';  // Osoba fizyczna
    case LocalLegalPersonUnit   = 'LP'; // Jednostka lokalna osoby prawnej
    case LocalNaturalPersonUnit = 'LF'; // Jednostka lokalna osoby fizycznej
}
