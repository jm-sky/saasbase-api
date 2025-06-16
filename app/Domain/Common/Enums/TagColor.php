<?php

namespace App\Domain\Common\Enums;

enum TagColor: string
{
    case DEFAULT         = 'default';
    case SUCCESS         = 'success';
    case SUCCESS_INTENSE = 'success-intense';
    case DANGER          = 'danger';
    case DANGER_INTENSE  = 'danger-intense';
    case INFO            = 'info';
    case INFO_INTENSE    = 'info-intense';
    case WARNING         = 'warning';
    case WARNING_INTENSE = 'warning-intense';
    case DARK            = 'dark';
    case DARK_INTENSE    = 'dark-intense';
    case NOTICE          = 'notice';
    case NOTICE_INTENSE  = 'notice-intense';
}
