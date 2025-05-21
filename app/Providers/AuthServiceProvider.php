<?php

namespace App\Providers;

use App\Domain\Common\Models\Address;
use App\Domain\Common\Models\BankAccount;
use App\Domain\Common\Policies\AddressPolicy;
use App\Domain\Common\Policies\BankAccountPolicy;
use App\Domain\Common\Policies\MediaPolicy;
use App\Domain\Exchanges\Models\Exchange;
use App\Domain\Exchanges\Policies\ExchangePolicy;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Policies\TenantPolicy;
use App\Domain\Users\Models\SecurityEvent;
use App\Domain\Users\Models\TrustedDevice;
use App\Domain\Users\Models\UserTableSetting;
use App\Domain\Users\Policies\SecurityEventPolicy;
use App\Domain\Users\Policies\TrustedDevicePolicy;
use App\Domain\Users\Policies\UserTableSettingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Tenant::class           => TenantPolicy::class,
        Exchange::class         => ExchangePolicy::class,
        UserTableSetting::class => UserTableSettingPolicy::class,
        TrustedDevice::class    => TrustedDevicePolicy::class,
        SecurityEvent::class    => SecurityEventPolicy::class,
        Address::class          => AddressPolicy::class,
        BankAccount::class      => BankAccountPolicy::class,
        Media::class            => MediaPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
