<?php

namespace App\Domain\Calendar\Enums;

enum EventVisibility: string
{
    case PUBLIC  = 'public';
    case PRIVATE = 'private';
}
