<?php

use App\Domain\Subscription\Enums\AddonType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('addon_packages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('stripe_price_id')->index();
            $table->text('description');
            $table->enum('type', array_map(fn (AddonType $type) => $type->value, AddonType::cases()));
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('duration_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addon_packages');
    }
};
