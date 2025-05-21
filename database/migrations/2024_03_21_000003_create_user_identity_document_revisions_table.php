<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('user_identity_document_revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('user_identity_documents')->cascadeOnDelete();
            $table->string('number');
            $table->string('country', 2);
            $table->date('issued_at')->nullable();
            $table->date('expires_at');
            $table->timestamp('changed_at')->nullable();
            $table->foreignUuid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_identity_document_revisions');
    }
};
