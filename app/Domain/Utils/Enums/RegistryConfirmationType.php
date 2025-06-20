<?php

namespace App\Domain\Utils\Enums;

enum RegistryConfirmationType: string
{
    case Regon       = 'regon';
    case Mf          = 'mf';
    case Vies        = 'vies';
    case CompanyData = 'companyData';
    case Address     = 'address';
    case BankAccount = 'bankAccount';
}
