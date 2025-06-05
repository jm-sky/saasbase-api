<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->boolean('is_all_day')->default(false);
            $table->string('location')->nullable();
            $table->string('color')->nullable();
            $table->string('status');
            $table->string('visibility');
            $table->string('timezone');
            $table->text('recurrence_rule')->nullable();
            $table->json('reminder_settings')->nullable();
            $table->foreignUlid('created_by_id')->constrained('users');
            $table->string('related_type')->nullable();
            $table->ulid('related_id')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->index(['tenant_id', 'start_at', 'end_at']);
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
