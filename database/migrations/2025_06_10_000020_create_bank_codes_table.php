<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('bank_codes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('country_code', 2);
            $table->string('bank_code');
            $table->string('bank_name');
            $table->string('swift')->nullable();
            $table->string('currency', 3);
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->unique(['country_code', 'bank_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_codes');
    }
};
