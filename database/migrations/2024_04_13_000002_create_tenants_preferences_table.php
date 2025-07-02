<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('tenant_preferences', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants');
            $table->string('currency', 3)->nullable();
            $table->boolean('require_2fa')->default(false);
            $table->boolean('invoice_auto_numbering')->default(true);
            $table->boolean('contractor_logo_fetching')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_preferences');
    }
};
