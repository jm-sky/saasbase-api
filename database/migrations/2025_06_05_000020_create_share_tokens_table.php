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
        Schema::create('share_tokens', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('token')->unique();
            $table->ulidMorphs('shareable');
            $table->boolean('only_for_authenticated')->default(false);
            $table->dateTime('expires_at')->nullable()->index();
            $table->dateTime('last_used_at')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->unsignedInteger('max_usage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_tokens');
    }
};
