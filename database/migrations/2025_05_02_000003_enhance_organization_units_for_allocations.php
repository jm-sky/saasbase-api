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
        // Enhance existing org_unit_user table for allocation workflows
        Schema::table('org_unit_user', function (Blueprint $table) {
            // Add tenant_id for multi-tenancy support
            $table->foreignUlid('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();

            // Add workflow-specific role level (optional, for allocation approvals)
            $table->enum('workflow_role_level', ['unit-member', 'unit-deputy', 'unit-owner', 'unit-admin'])
                ->nullable()->after('role')
            ;

            // Add primary membership flag
            $table->boolean('is_primary')->default(false)->after('workflow_role_level');

            // Add validity period for memberships
            $table->timestamp('valid_from')->useCurrent()->after('is_primary');
            $table->timestamp('valid_until')->nullable()->after('valid_from');

            // Add indexes for new fields
            $table->index('tenant_id');
            $table->index('workflow_role_level');
            $table->index('is_primary');
            $table->index(['valid_from', 'valid_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove enhancements from org_unit_user table
        Schema::table('org_unit_user', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropIndex(['workflow_role_level']);
            $table->dropIndex(['is_primary']);
            $table->dropIndex(['valid_from', 'valid_until']);

            $table->dropColumn([
                'tenant_id',
                'workflow_role_level',
                'is_primary',
                'valid_from',
                'valid_until',
            ]);
        });
    }
};
