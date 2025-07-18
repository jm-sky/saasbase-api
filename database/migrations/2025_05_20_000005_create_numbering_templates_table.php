<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('numbering_templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('invoice_type');
            $table->string('format');
            $table->unsignedInteger('next_number')->default(1);
            $table->enum('reset_period', ['never', 'yearly', 'monthly'])->default('never');
            $table->string('prefix')->nullable();
            $table->string('suffix')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numbering_templates');
    }
};
