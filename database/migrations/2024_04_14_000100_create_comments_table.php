<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->uuid('commentable_id');
            $table->string('commentable_type');
            $table->jsonb('meta')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['commentable_id', 'commentable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
