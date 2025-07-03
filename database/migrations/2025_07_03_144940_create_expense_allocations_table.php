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
        Schema::create('expense_allocations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('expense_id')->constrained('expenses')->cascadeOnDelete();
            $table->decimal('amount', 19, 4);
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'allocated'])->default('pending');
            $table->timestamps();

            // Indexes
            $table->index('tenant_id');
            $table->index('expense_id');
            $table->index('status');
            $table->index(['expense_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_allocations');
    }
};
