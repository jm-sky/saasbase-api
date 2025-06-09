<?php

namespace App\Domain\Products\Enums;

enum ProductType: string
{
    case SERVICE = 'service';
    case PRODUCT = 'product';
}
