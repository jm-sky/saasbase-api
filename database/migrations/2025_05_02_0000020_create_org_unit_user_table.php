<?php

// database/migrations/2025_05_02_000002_create_org_unit_user_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('org_unit_user', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Core relationships
            $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUlid('organization_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('position_id')->nullable()->constrained('positions')->nullOnDelete();

            // Role and workflow information
            $table->string('role');
            $table->enum('workflow_role_level', ['unit-member', 'unit-deputy', 'unit-owner', 'unit-admin'])->nullable();

            // Membership flags
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);

            // Validity period (using timestamps for precision)
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_until')->nullable();

            // Additional information
            $table->text('notes')->nullable();

            // Standard timestamps
            $table->timestamps();

            // Indexes and constraints
            $table->unique(['organization_unit_id', 'user_id']);
            $table->index('tenant_id');
            $table->index('workflow_role_level');
            $table->index('is_primary');
            $table->index('is_active');
            $table->index(['valid_from', 'valid_until']);
            $table->index(['tenant_id', 'position_id']);
            $table->index(['user_id', 'position_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_unit_user');
    }
};
