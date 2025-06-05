<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('default_project_statuses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('color', 7);
            $table->integer('sort_order');
            $table->string('category')->nullable();
            $table->boolean('is_default')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('default_project_statuses');
    }
};
