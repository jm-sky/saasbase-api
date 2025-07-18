<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('temp_id')->nullable();
            $table->ulid('tenant_id')->nullable()->index();
            $table->ulid('chat_room_id')->index();
            $table->ulid('user_id')->index();
            $table->ulid('parent_id')->nullable()->index(); // for threads
            $table->text('content'); // markdown content
            $table->string('role')->default('user');
            $table->boolean('is_ai')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
