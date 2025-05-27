<?php

namespace App\Services\GusLookup\Enums;

enum EntityType: string
{
    case P  = 'P';   // Jednostka prawna (= osoba prawna lub jednostka organizacyjna nieposiadająca osobowości prawnej, np. spółka cywilna)
    case F  = 'F';   // Jedn. fizyczna (= os. fizyczna prowadząca działalność gospodarczą)
    case LP = 'LP'; // Jednostka lokalna jednostki prawnej
    case LF = 'LF'; // Jednostka lokalna jednostki fizycznej
}
