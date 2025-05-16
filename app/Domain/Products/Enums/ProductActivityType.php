<?php

namespace App\Domain\Products\Enums;

enum ProductActivityType: string
{
    case Created         = 'product.created';
    case Updated         = 'product.updated';
    case Deleted         = 'product.deleted';
    case CategoryChanged = 'product.category.changed';
    case PriceUpdated    = 'product.price.updated';
    case StockUpdated    = 'product.stock.updated';
}
