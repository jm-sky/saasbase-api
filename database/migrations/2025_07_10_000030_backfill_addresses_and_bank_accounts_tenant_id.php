<?php

use App\Domain\Common\Models\Contact;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * Address/BankAccount never had anything populating tenant_id on create
 * (the morphMany relations on HaveAddresses/HaveBankAccounts always
 * instantiate the base Address/BankAccount model, not the tenant-scoped
 * subclasses), so every existing row has tenant_id = NULL. Now that both
 * base models use BelongsToTenant (which adds a strict tenant_id match
 * global scope), those rows would become invisible to everyone unless
 * backfilled here.
 *
 * User-owned rows are backfilled from user_tenants; a user belonging to
 * more than one tenant is inherently ambiguous for a row that predates
 * tenant scoping, so we best-effort pick one membership rather than
 * leaving the row permanently orphaned.
 */
return new class() extends Migration {
    public function up(): void
    {
        DB::table('addresses')
            ->whereNull('tenant_id')
            ->where('addressable_type', Tenant::class)
            ->update(['tenant_id' => DB::raw('addressable_id')])
        ;

        DB::table('bank_accounts')
            ->whereNull('tenant_id')
            ->where('bankable_type', Tenant::class)
            ->update(['tenant_id' => DB::raw('bankable_id')])
        ;

        $this->backfillFromOwner('addresses', 'addressable', Contractor::class, 'contractors');
        $this->backfillFromOwner('addresses', 'addressable', Contact::class, 'contacts');
        $this->backfillFromOwner('bank_accounts', 'bankable', Contractor::class, 'contractors');

        $this->backfillFromUserTenants('addresses', 'addressable');
        $this->backfillFromUserTenants('bank_accounts', 'bankable');
    }

    public function down(): void
    {
        // Data backfill only; not reversible.
    }

    private function backfillFromOwner(string $table, string $morphName, string $ownerClass, string $ownerTable): void
    {
        DB::statement(<<<SQL
            UPDATE {$table}
            SET tenant_id = owner.tenant_id
            FROM {$ownerTable} AS owner
            WHERE {$table}.tenant_id IS NULL
              AND {$table}.{$morphName}_type = ?
              AND {$table}.{$morphName}_id = owner.id
        SQL, [$ownerClass]);
    }

    private function backfillFromUserTenants(string $table, string $morphName): void
    {
        DB::statement(<<<SQL
            UPDATE {$table}
            SET tenant_id = membership.tenant_id
            FROM (
                SELECT DISTINCT ON (user_id) user_id, tenant_id
                FROM user_tenants
                WHERE deleted_at IS NULL
                ORDER BY user_id, created_at ASC
            ) AS membership
            WHERE {$table}.tenant_id IS NULL
              AND {$table}.{$morphName}_type = ?
              AND {$table}.{$morphName}_id = membership.user_id
        SQL, [App\Domain\Auth\Models\User::class]);
    }
};
