<?php

namespace App\Domain\Calendar\Enums;

enum AttendeeResponseStatus: string
{
    case ATTENDING = 'attending';
    case MAYBE     = 'maybe';
    case DECLINED  = 'declined';
}
