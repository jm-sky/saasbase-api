<?php

namespace App\Domain\Common\Enums;

use App\Traits\HasEnumValues;

enum AdvancedFilterOperator: string
{
    use HasEnumValues;

    case Equals             = 'eq';
    case NotEquals          = 'ne';
    case NotEqualsAlt       = 'neq';
    case GreaterThan        = 'gt';
    case GreaterThanOrEqual = 'gte';
    case LessThan           = 'lt';
    case LessThanOrEqual    = 'lte';
    case From               = 'from';
    case To                 = 'to';
    case Like               = 'like';
    case NotLike            = 'nlike';
    case NotLikeAlt         = 'notlike';
    case StartsWith         = 'startswith';
    case EndsWith           = 'endswith';
    case Regex              = 'regex';
    case IsNull             = 'null';
    case IsNotNull          = 'notnull';
    case IsNullish          = 'nullish';
    case In                 = 'in';
    case NotIn              = 'nin';
    case NotInAlt           = 'notin';
    case Between            = 'between';
}
