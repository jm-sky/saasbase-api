<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('tenant_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('inviter_id');
            $table->string('email');
            $table->string('role');
            $table->string('token')->unique();
            $table->string('status'); // pending, accepted, expired
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('expires_at');
            $table->timestamps();
            $table->index('tenant_id');
            $table->index('email');
            $table->index('token');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_invitations');
    }
};
