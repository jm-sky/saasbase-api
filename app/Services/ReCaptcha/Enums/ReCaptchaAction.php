<?php

namespace App\Services\ReCaptcha\Enums;

enum ReCaptchaAction: string
{
    case LOGIN           = 'login';
    case REGISTER        = 'register';
    case FORGOT_PASSWORD = 'forgot_password';
    case RESET_PASSWORD  = 'reset_password';
}
