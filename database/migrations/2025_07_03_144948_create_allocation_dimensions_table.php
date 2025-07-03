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
        Schema::create('allocation_dimensions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('allocation_id')->constrained('expense_allocations')->cascadeOnDelete();
            $table->enum('dimension_type', ['HA', 'LO', 'PD', 'PR', 'RS', 'RTR', 'RY', 'ST', 'TP', 'UM', 'UR']);
            $table->string('dimension_id'); // ULID of the related dimension entity
            $table->timestamps();

            // Indexes
            $table->index('allocation_id');
            $table->index('dimension_type');
            $table->index('dimension_id');
            $table->index(['allocation_id', 'dimension_type']);
            $table->index(['dimension_type', 'dimension_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocation_dimensions');
    }
};
