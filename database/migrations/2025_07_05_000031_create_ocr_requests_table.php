<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('ocr_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('processable_type');
            $table->ulid('processable_id');
            $table->ulid('media_id');
            $table->string('external_document_id')->nullable();
            $table->string('status');
            $table->jsonb('result')->nullable();
            $table->jsonb('errors')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->ulid('created_by');
            $table->timestamps();

            $table->index(['processable_type', 'processable_id']);
            $table->foreign('media_id')->references('id')->on('media')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocr_requests');
    }
};
