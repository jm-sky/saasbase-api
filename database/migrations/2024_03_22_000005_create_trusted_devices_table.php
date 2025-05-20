<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->string('device_name')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->string('ip_address', 45);
            $table->timestamp('trusted_until')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'device_name']);
            $table->unique(['user_id', 'token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_devices');
    }
};
