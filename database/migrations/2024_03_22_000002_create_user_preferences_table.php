<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('language')->nullable();
            $table->string('decimal_separator')->nullable();
            $table->string('date_format')->nullable();
            $table->string('dark_mode')->nullable();
            $table->boolean('is_sound_enabled')->nullable();
            $table->boolean('is_profile_public')->default(false);
            $table->json('field_visibility')->nullable();
            $table->json('visibility_per_tenant')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
