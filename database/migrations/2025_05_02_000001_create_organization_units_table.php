<?php

// database/migrations/2025_05_02_000001_create_organization_units_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('organization_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('organization_units')->cascadeOnDelete();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->timestamps();

            // TODO: unique [tenant_id, name]
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_units');
    }
};
