<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('billing_info', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulidMorphs('billable');
            $table->string('name');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('postal_code');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country', 2);
            $table->string('vat_id')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('email_for_billing')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_info');
    }
};
