<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('application_invitations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('inviter_id');
            $table->ulid('invited_user_id')->nullable();
            $table->string('email');
            $table->string('token')->unique();
            $table->string('status'); // pending, accepted, rejected, expired, canceled
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('expires_at');
            $table->timestamps();
            $table->index('email');
            $table->index('token');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_invitations');
    }
};
