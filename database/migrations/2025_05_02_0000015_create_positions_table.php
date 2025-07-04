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
        Schema::create('positions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUlid('organization_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('position_category_id')->constrained()->restrictOnDelete();
            $table->string('role_name')->nullable(); // Links to Spatie role
            $table->string('name');
            $table->text('description')->nullable();

            // Position flags
            $table->boolean('is_director')->default(false);
            $table->boolean('is_learning')->default(false);
            $table->boolean('is_temporary')->default(false);

            // Additional metadata
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes and constraints
            $table->unique(['tenant_id', 'organization_unit_id', 'name']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'is_director']);
            $table->index(['tenant_id', 'is_learning']);
            $table->index(['tenant_id', 'organization_unit_id']);

            // Foreign key for role_name (optional constraint)
            // $table->foreign('role_name')->references('name')->on('roles')->nullOnDelete();
            $table->foreign(['tenant_id', 'role_name'])->references(['tenant_id', 'name'])->on('roles')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
