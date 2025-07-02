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
        Schema::create('allocation_organization_unit_memberships', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('organization_unit_id')->constrained('allocation_organization_units')->cascadeOnDelete();
            $table->enum('role_level', ['unit-member', 'unit-deputy', 'unit-owner', 'unit-admin'])->default('unit-member');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('tenant_id');
            $table->index('user_id');
            $table->index('organization_unit_id');
            $table->index('role_level');
            $table->index(['valid_from', 'valid_until']);

            // Unique constraint: one user can have only one membership per organization unit
            $table->unique(['user_id', 'organization_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocation_organization_unit_memberships');
    }
};
