<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('user_table_settings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('entity');
            $table->string('name')->nullable();
            $table->json('config');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'entity', 'name', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_table_settings');
    }
};
