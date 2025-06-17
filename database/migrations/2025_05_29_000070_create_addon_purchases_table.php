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
        Schema::create('addon_purchases', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulidMorphs('billable');
            $table->ulid('addon_package_id');
            $table->string('stripe_invoice_item_id')->nullable();
            $table->dateTime('purchased_at');
            $table->dateTime('expires_at')->nullable();
            $table->integer('quantity')->nullable();
            $table->float('amount')->nullable();
            $table->string('currency')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addon_purchases');
    }
};
