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
        Schema::create('approval_workflow_steps', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->integer('step_order');
            $table->string('name');
            $table->boolean('require_all_approvers')->default(false);
            $table->integer('min_approvers')->default(1);
            $table->timestamps();

            // Unique constraint to ensure step order is unique within a workflow
            $table->unique(['workflow_id', 'step_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_workflow_steps');
    }
};
