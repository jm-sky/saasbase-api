<?php

// database/migrations/2025_05_02_000002_create_org_unit_user_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('org_unit_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->timestamps();

            $table->unique(['organization_unit_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_unit_user');
    }
}; // database/migrations/2025_05_02_000002_create_org_unit_user_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('org_unit_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->timestamps();

            $table->unique(['organization_unit_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_unit_user');
    }
};
