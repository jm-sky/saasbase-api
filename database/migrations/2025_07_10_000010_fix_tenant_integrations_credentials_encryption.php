<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/*
 * TenantIntegration::$casts declared 'credentials' => 'encrypted:json', but a
 * conflicting Attribute accessor on the model silently overrode it, so
 * credentials (Azure keys, KSeF tokens, etc.) were stored as plain JSON, not
 * encrypted — despite the column looking like it should be. This migration:
 *
 *  1. Widens the column from jsonb to text, because Laravel's encrypted-cast
 *     payload is a base64 blob, not valid JSON, and would be rejected by a
 *     jsonb column once the model actually starts encrypting.
 *  2. Re-encrypts any existing plaintext rows in place so nothing is lost.
 *
 * The corresponding model fix (removing the overriding accessor) is a
 * separate code change in App\Domain\Tenant\Models\TenantIntegration.
 */
return new class() extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE tenant_integrations ALTER COLUMN credentials TYPE text USING credentials::text');

        $rows = DB::table('tenant_integrations')->whereNotNull('credentials')->get(['id', 'credentials']);

        foreach ($rows as $row) {
            if (self::looksEncrypted($row->credentials)) {
                continue;
            }

            DB::table('tenant_integrations')
                ->where('id', $row->id)
                ->update(['credentials' => Crypt::encryptString($row->credentials)])
            ;
        }
    }

    public function down(): void
    {
        $rows = DB::table('tenant_integrations')->whereNotNull('credentials')->get(['id', 'credentials']);

        foreach ($rows as $row) {
            if (!self::looksEncrypted($row->credentials)) {
                continue;
            }

            try {
                $plain = Crypt::decryptString($row->credentials);
            } catch (Throwable) {
                continue;
            }

            DB::table('tenant_integrations')
                ->where('id', $row->id)
                ->update(['credentials' => $plain])
            ;
        }

        DB::statement('ALTER TABLE tenant_integrations ALTER COLUMN credentials TYPE jsonb USING credentials::jsonb');
    }

    private static function looksEncrypted(?string $value): bool
    {
        if (null === $value || '' === $value) {
            return false;
        }

        $decoded = json_decode((string) base64_decode($value, true), true);

        return is_array($decoded) && isset($decoded['iv'], $decoded['value'], $decoded['mac']);
    }
};
