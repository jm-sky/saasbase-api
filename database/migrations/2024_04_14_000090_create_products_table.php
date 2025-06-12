<?php

use App\Domain\Products\Enums\ProductType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id');
            $table->string('name');
            $table->string('symbol', 100)->nullable();
            $table->enum('type', array_map(fn ($case) => $case->value, ProductType::cases()));
            $table->text('description')->nullable();
            $table->ulid('unit_id')->nullable();
            $table->decimal('price_net', 10, 2);
            $table->ulid('vat_rate_id')->nullable();
            $table->string('ean', 13)->nullable();
            $table->string('external_id', 100)->nullable();
            $table->string('source_system', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('measurement_units')->onDelete('restrict');
            $table->foreign('vat_rate_id')->references('id')->on('vat_rates')->onDelete('restrict');

            $table->unique(['tenant_id', 'symbol']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
