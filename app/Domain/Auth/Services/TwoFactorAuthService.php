<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function generateRecoveryCodes(): array
    {
        return Collection::times(8, fn () => $this->generateRecoveryCode())->all();
    }

    private function generateRecoveryCode(): string
    {
        return sprintf(
            '%s-%s-%s-%s',
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(4))
        );
    }

    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey(
            $secret,
            $code,
            config('auth.2fa.window', 1) // Allow 1 time slice drift by default
        );
    }

    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $settings = $user->settings;

        if (!$settings || !$settings->two_factor_recovery_codes) {
            return false;
        }

        $recoveryCodes = json_decode(
            Crypt::decryptString($settings->two_factor_recovery_codes),
            true
        );

        $position = array_search($code, $recoveryCodes);

        if (false === $position) {
            return false;
        }

        // Remove used code
        unset($recoveryCodes[$position]);
        $settings->two_factor_recovery_codes = Crypt::encryptString(
            json_encode(array_values($recoveryCodes))
        );
        $settings->save();

        return true;
    }

    public function enableTwoFactor(User $user, string $code): bool
    {
        $settings = $user->settings;

        if (!$settings || !$settings->two_factor_secret) {
            return false;
        }

        $secret = Crypt::decryptString($settings->two_factor_secret);

        if (!$this->verifyCode($secret, $code)) {
            return false;
        }

        $settings->update([
            'two_factor_enabled'   => true,
            'two_factor_confirmed' => true,
        ]);

        return true;
    }

    public function disableTwoFactor(User $user): void
    {
        $settings = $user->settings;

        if (!$settings) {
            return;
        }

        $settings->update([
            'two_factor_enabled'        => false,
            'two_factor_confirmed'      => false,
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    public function generateTwoFactorSetup(User $user): array
    {
        $settings = $user->settings;

        if (!$settings) {
            return [];
        }

        $secret        = $this->generateSecretKey();
        $recoveryCodes = $this->generateRecoveryCodes();

        $settings->update([
            'two_factor_secret'         => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
            'two_factor_enabled'        => false,
            'two_factor_confirmed'      => false,
        ]);

        return [
            'secret'         => $secret,
            'recovery_codes' => $recoveryCodes,
            'qr_code_url'    => $this->google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $secret
            ),
        ];
    }
}
