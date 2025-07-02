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
        Schema::create('tenant_dimension_configurations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('dimension_type', ['HA', 'LO', 'PD', 'PR', 'RS', 'RTR', 'RY', 'ST', 'TP', 'UM', 'UR']);
            $table->boolean('is_enabled')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('tenant_id');
            $table->index('dimension_type');
            $table->index(['tenant_id', 'is_enabled']);
            $table->index(['tenant_id', 'display_order']);

            // Unique constraint: one configuration per tenant per dimension type
            $table->unique(['tenant_id', 'dimension_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_dimension_configurations');
    }
};
