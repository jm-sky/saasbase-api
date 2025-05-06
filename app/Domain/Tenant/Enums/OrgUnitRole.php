<?php

namespace App\Domain\Tenant\Enums;

enum OrgUnitRole: string
{
    case Owner          = 'owner';           // Właściciel organizacji
    case CEO            = 'ceo';             // Dyrektor generalny
    case DepartmentHead = 'department-head'; // Kierownik działu
    case TeamLead       = 'team-lead';       // Kierownik zespołu
    case Employee       = 'employee';         // Pracownik
}
