<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `number` had no uniqueness constraint at all (not even per-tenant), and
 * the client supplies the number directly — nothing on the backend
 * generates or deduplicates it. This doesn't fix the missing server-side
 * number generation (a larger change tying into NumberingTemplate), but it
 * closes the immediate gap: two invoices with the same number could be
 * created in the same tenant.
 */
return new class() extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unique(['tenant_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'number']);
        });
    }
};
