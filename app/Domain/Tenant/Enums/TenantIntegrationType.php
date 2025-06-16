<?php

namespace App\Domain\Tenant\Enums;

enum TenantIntegrationType: string
{
    case AzureAi           = 'azureAi';
    case S3                = 's3';
    case RegonApi          = 'regonApi';
    case GoogleCalendar    = 'googleCalendar';
    case MicrosoftCalendar = 'microsoftCalendar';
    case Jira              = 'jira';
    case Ksef              = 'ksef';
    case EDelivery         = 'eDelivery';
}
