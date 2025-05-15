<?php

namespace App\Domain\Common\Enums;

enum AddressType: string
{
    case RESIDENCE         = 'residence';
    case BILLING           = 'billing';
    case REGISTERED_OFFICE = 'registeredOffice';
    case CORRESPONDENCE    = 'correspondence';
    case CONTACT           = 'contact';
}
