<?php

namespace App\Domain\Tenant\Enums;

enum OrgUnitRole: string
{
    case CEO = 'ceo';
    case DepartmentHead = 'department_head';
    case TeamLead = 'team_lead';
    case Employee = 'employee'; 
}
