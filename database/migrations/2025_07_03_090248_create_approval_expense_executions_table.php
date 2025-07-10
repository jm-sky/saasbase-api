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
        Schema::create('approval_expense_executions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('expense_id')->constrained('expenses')->cascadeOnDelete();
            $table->foreignUlid('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->foreignUlid('current_step_id')->nullable()->constrained('approval_workflow_steps')->nullOnDelete();
            $table->foreignUlid('initiator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index(['expense_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_expense_executions');
    }
};
