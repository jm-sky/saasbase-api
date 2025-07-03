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
        Schema::create('approval_step_approvers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('step_id')->constrained('approval_workflow_steps')->cascadeOnDelete();
            $table->enum('approver_type', ['user', 'unit_role', 'system_permission']);
            $table->string('approver_value');
            $table->foreignUlid('organization_unit_id')->nullable()->constrained('organization_units')->cascadeOnDelete();
            $table->boolean('can_delegate')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('organization_unit_id');

            // Unique constraint to prevent duplicate approver configurations
            $table->unique(['step_id', 'approver_type', 'approver_value', 'organization_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_step_approvers');
    }
};
