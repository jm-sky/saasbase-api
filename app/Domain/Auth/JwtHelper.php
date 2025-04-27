namespace App\Domain\Auth;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Config;
use App\Domain\Auth\Models\User;

class JwtHelper
{
    const REFRESH_TOKEN_TTL = 7776000;

    public static function getDefaultPayload(User $user, bool $twoFactorPassed = false): array
    {
        $payload = [
            'ev' => $user->hasVerifiedEmail() ? 1 : 0,
        ];

        if ($user->isTwoFactorEnabled()) {
            $payload['2fa'] = $twoFactorPassed ? 2 : 1;
        }

        return $payload;
    }

    public static function createTokenWithoutTenant(User $user, bool $twoFactorPassed = false): string
    {
        $payload = self::getDefaultPayload($user, $twoFactorPassed);
        return JWTAuth::fromUser($user, $payload);
    }

    public static function createTokenWithTenant(User $user, string $tenantId, bool $twoFactorPassed = false): string
    {
        $payload = array_merge(self::getDefaultPayload($user, $twoFactorPassed), [
            'tid' => $tenantId,
        ]);
        return JWTAuth::fromUser($user, $payload);
    }

    public static function createRefreshToken(User $user): string
    {
        $ttl = self::getRefreshTokenTTL();
        $payload = self::getDefaultPayload($user);
        return JWTAuth::claims($payload)->setTTL($ttl)->fromUser($user);
    }

    public static function getRefreshTokenTTL(): int
    {
        return Config::get('jwt.refresh_ttl', self::REFRESH_TOKEN_TTL);
    }

    public static function generateToken(User $user, string $tenantId = null, bool $twoFactorPassed = false, bool $isRefreshToken = false): string
    {
        if ($isRefreshToken) {
            return self::createRefreshToken($user);
        }

        if ($tenantId) {
            return self::createTokenWithTenant($user, $tenantId, $twoFactorPassed);
        }

        return self::createTokenWithoutTenant($user, $twoFactorPassed);
    }
}
