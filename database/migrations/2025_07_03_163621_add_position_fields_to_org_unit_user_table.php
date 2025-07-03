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
        Schema::table('org_unit_user', function (Blueprint $table) {
            $table->foreignUlid('position_id')->nullable()->after('workflow_role_level')->constrained('positions')->nullOnDelete();
            $table->date('start_date')->nullable()->after('position_id');
            $table->date('end_date')->nullable()->after('start_date');
            $table->text('notes')->nullable()->after('end_date');

            // Indexes
            $table->index(['tenant_id', 'position_id']);
            $table->index(['user_id', 'position_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('org_unit_user', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropIndex(['tenant_id', 'position_id']);
            $table->dropIndex(['user_id', 'position_id']);

            $table->dropColumn([
                'position_id',
                'start_date',
                'end_date',
                'notes',
            ]);
        });
    }
};
