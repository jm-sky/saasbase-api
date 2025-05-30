<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('edoreczenia_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('message_id')->nullable();
            $table->string('subject')->nullable();
            $table->string('status');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->json('headers_json')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'provider', 'message_id']);
            $table->index(['tenant_id', 'direction', 'status']);
        });

        Schema::create('edoreczenia_certificates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('file_path');
            $table->string('fingerprint');
            $table->string('subject_cn');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'provider']);
            $table->index('valid_to');
        });

        Schema::create('edoreczenia_message_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('edoreczenia_message_id')->constrained('edoreczenia_messages')->cascadeOnDelete();
            $table->string('file_name');
            $table->bigInteger('file_size');
            $table->string('file_path');
            $table->timestamps();
            $table->softDeletes();

            $table->index('edoreczenia_message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edoreczenia_message_attachments');
        Schema::dropIfExists('edoreczenia_certificates');
        Schema::dropIfExists('edoreczenia_messages');
    }
};
