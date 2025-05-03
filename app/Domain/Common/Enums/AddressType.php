<?php

namespace App\Domain\Common\Enums;

enum AddressType: string
{
    case RESIDENCE         = 'residence';
    case BILLING           = 'billing';
    case REGISTERED_OFFICE = 'registered_office';
    case CORRESPONDENCE    = 'correspondence';
    case DOMICILE          = 'domicile';
}
