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
        Schema::create('approval_expense_decisions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('execution_id')->constrained('approval_expense_executions')->cascadeOnDelete();
            $table->foreignUlid('step_id')->constrained('approval_workflow_steps')->cascadeOnDelete();
            $table->foreignUlid('approver_id')->constrained('users')->cascadeOnDelete();
            $table->enum('decision', ['approved', 'rejected']);
            $table->text('reason')->nullable();
            $table->timestamp('decided_at');
            $table->timestamps();

            // Indexes
            $table->index('approver_id');
            $table->index(['execution_id', 'decision']);

            // Unique constraint to prevent duplicate decisions from same approver on same step
            $table->unique(['execution_id', 'step_id', 'approver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_expense_decisions');
    }
};
