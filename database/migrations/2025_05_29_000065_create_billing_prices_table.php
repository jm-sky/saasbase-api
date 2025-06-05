<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('billing_prices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulidMorphs('priceable');
            $table->string('stripe_price_id')->nullable();
            $table->enum('billing_period', ['monthly', 'yearly']);
            $table->integer('price_cents');
            $table->string('currency', 3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['priceable_id', 'priceable_type', 'billing_period']);
            $table->index('stripe_price_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_prices');
    }
};
