<?php

namespace App\Domain\Tenant\Enums;

enum DefaultPositionCategory: string
{
    case Director = 'Director';
    case Manager  = 'Manager';
    case Employee = 'Employee';
    case Trainee  = 'Trainee';
}
