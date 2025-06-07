<?php

namespace App\Services\ReCaptcha;

use Illuminate\Support\Facades\Http;

class ReCaptchaService
{
    protected const RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public function verify(string $token, string $expectedAction, float $minScore = 0.5, ?string $ip = null): bool
    {
        $response = Http::asForm()->post(self::RECAPTCHA_VERIFY_URL, [
            'secret'   => config('services.recaptcha.secret'),
            'response' => $token,
            'remoteip' => $ip,
        ]);

        $result = $response->json();

        return $result['success']
            && ($result['score'] ?? 0) >= $minScore
            && ($result['action'] ?? null) === $expectedAction;
    }
}
