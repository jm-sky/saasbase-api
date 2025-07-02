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
        Schema::create('allocation_organization_units', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('name');
            $table->text('description')->nullable();
            $table->ulid('parent_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('tenant_id');
            $table->index('code');
            $table->index('parent_id');
            $table->index('is_active');

            // Unique constraint: code must be unique per tenant (global records have tenant_id = null)
            $table->unique(['code', 'tenant_id']);
        });

        // Add self-referencing foreign key constraint after table creation
        Schema::table('allocation_organization_units', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('allocation_organization_units')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraint first if table exists
        if (Schema::hasTable('allocation_organization_units')) {
            Schema::table('allocation_organization_units', function (Blueprint $table) {
                $table->dropForeign(['parent_id']);
            });
        }

        Schema::dropIfExists('allocation_organization_units');
    }
};
