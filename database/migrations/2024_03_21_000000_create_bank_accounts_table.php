<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id')->nullable();
            $table->ulidMorphs('bankable');
            $table->string('iban');
            $table->string('country', 2)->nullable();
            $table->string('swift')->nullable();
            $table->string('bank_name')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('currency')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'bankable_type', 'bankable_id', 'is_default'], 'bank_accounts_default_idx');
            $table->index('iban');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
