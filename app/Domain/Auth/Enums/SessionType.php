<?php

namespace App\Domain\Auth\Enums;

enum SessionType: string
{
    case JWT    = 'jwt';
    case COOKIE = 'cookie';
}
