<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->uuid('unit_id');
            $table->decimal('price_net', 10, 2);
            $table->uuid('vat_rate_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('restrict');
            $table->foreign('vat_rate_id')->references('id')->on('vat_rates')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
