<?php

namespace App\Domain\Contractors\Enums;

enum ContractorType: string
{
    case COMPANY      = 'company';
    case INDIVIDUAL   = 'individual';
    case ORGANIZATION = 'organization';
    case INSTITUTION  = 'institution';
    case GOVERNMENT   = 'government';
    case NON_PROFIT   = 'non_profit';
    case OTHER        = 'other';
}
