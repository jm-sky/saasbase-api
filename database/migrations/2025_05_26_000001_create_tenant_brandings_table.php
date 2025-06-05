<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('tenant_brandings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id')->unique();
            $table->string('color_primary')->nullable();
            $table->string('color_secondary')->nullable();
            $table->string('short_name')->nullable();
            $table->string('theme')->default('system');
            $table->string('pdf_accent_color')->nullable();
            $table->text('email_signature_html')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade')
            ;
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_brandings');
    }
};
