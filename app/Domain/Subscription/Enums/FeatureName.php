<?php

namespace App\Domain\Subscription\Enums;

enum FeatureName: string
{
    case MAX_USERS               = 'max_users';
    case STORAGE_MB              = 'storage_mb';
    case MAX_INVOICES            = 'max_invoices';
    case GUS_VIES_REQUESTS       = 'gus_vies_requests';
    case KSEF_INTEGRATION        = 'ksef_integration';
    case EDORECZENIA_INTEGRATION = 'edoreczenia_integration';
    case UNLIMITED_GUS_VIES      = 'unlimited_gus_vies';
    case AUTO_CONTRACTOR_LOGO    = 'auto_contractor_logo';

    public function description(): string
    {
        return match ($this) {
            self::MAX_USERS               => 'Maximum number of users allowed',
            self::STORAGE_MB              => 'Storage space in megabytes',
            self::MAX_INVOICES            => 'Maximum number of invoices per month',
            self::GUS_VIES_REQUESTS       => 'Number of GUS/VIES requests allowed',
            self::KSEF_INTEGRATION        => 'Access to KSEF integration',
            self::EDORECZENIA_INTEGRATION => 'Access to e-Doręczenia integration',
            self::UNLIMITED_GUS_VIES      => 'Unlimited GUS/VIES requests',
            self::AUTO_CONTRACTOR_LOGO    => 'Automatic contractor logo fetching from GUS/VIES',
        };
    }

    public function type(): string
    {
        return match ($this) {
            self::MAX_USERS, self::STORAGE_MB, self::MAX_INVOICES, self::GUS_VIES_REQUESTS => 'integer',
            self::KSEF_INTEGRATION, self::EDORECZENIA_INTEGRATION,
            self::UNLIMITED_GUS_VIES, self::AUTO_CONTRACTOR_LOGO => 'boolean',
        };
    }

    public function defaultValue(): string
    {
        return match ($this) {
            self::MAX_USERS, self::STORAGE_MB, self::MAX_INVOICES, self::GUS_VIES_REQUESTS => '0',
            self::KSEF_INTEGRATION, self::EDORECZENIA_INTEGRATION,
            self::UNLIMITED_GUS_VIES, self::AUTO_CONTRACTOR_LOGO => 'false',
        };
    }
}
