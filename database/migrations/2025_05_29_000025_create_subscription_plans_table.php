<?php

use App\Domain\Subscription\Enums\BillingInterval;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('stripe_product_id')->nullable()->index();
            $table->string('stripe_price_id')->nullable()->index();
            $table->enum('interval', array_map(fn (BillingInterval $interval) => $interval->value, BillingInterval::cases()));
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('PLN');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};