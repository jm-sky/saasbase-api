<?php

namespace App\Domain\Common\Enums;

enum OcrRequestStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Failed     = 'failed';
}
