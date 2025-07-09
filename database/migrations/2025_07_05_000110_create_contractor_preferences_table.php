<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('contractor_preferences', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id');
            $table->ulid('contractor_id');
            $table->ulid('default_payment_method_id')->nullable();
            $table->string('default_currency', 3)->nullable();
            $table->string('default_language', 2)->nullable();
            $table->integer('default_payment_days')->nullable();
            $table->json('default_tags')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('contractor_id')->references('id')->on('contractors')->onDelete('cascade');
            $table->foreign('default_payment_method_id')->references('id')->on('payment_methods')->nullOnDelete();
            $table->unique('contractor_id'); // Each contractor can have at most one set of preferences
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractor_preferences');
    }
};
