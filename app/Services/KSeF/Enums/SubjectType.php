<?php

namespace App\Services\KSeF\Enums;

enum SubjectType: string
{
    case SUBJECT_BY         = 'subject1';
    case SUBJECT_TO         = 'subject2';
    case SUBJECT_BY_K       = 'subjectByK';
    case SUBJECT_TO_K       = 'subjectToK';
    case SUBJECT_AUTHORIZED = 'subjectAuthorized';
    case SUBJECT_OTHER      = 'subjectOther';

    public function description(): string
    {
        return match ($this) {
            self::SUBJECT_BY         => 'Invoice issuer',
            self::SUBJECT_TO         => 'Invoice recipient',
            self::SUBJECT_BY_K       => 'Invoice issuer (K)',
            self::SUBJECT_TO_K       => 'Invoice recipient (K)',
            self::SUBJECT_AUTHORIZED => 'Authorized subject',
            self::SUBJECT_OTHER      => 'Other subject',
        };
    }
}
