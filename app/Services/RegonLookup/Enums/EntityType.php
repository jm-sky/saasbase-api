<?php

namespace App\Services\RegonLookup\Enums;

enum EntityType: string
{
    case P  = 'P';  // Osoba prawna
    case F  = 'F';  // Osoba fizyczna
    case LP = 'LP'; // Jednostka lokalna osoby prawnej
    case LF = 'LF'; // Jednostka lokalna osoby fizycznej
}
