<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('file_name');
            $table->string('file_url');
            $table->string('file_type');
            $table->integer('file_size');
            $table->uuid('attachmentable_id');
            $table->string('attachmentable_type');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['attachmentable_id', 'attachmentable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
