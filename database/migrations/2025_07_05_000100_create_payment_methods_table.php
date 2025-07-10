<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('code');
            $table->integer('payment_days')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'name']); // Prevent duplicate names per tenant/global
            $table->unique(['tenant_id', 'code']); // Prevent duplicate codes per tenant/global
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
