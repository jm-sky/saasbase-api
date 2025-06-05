<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('user_personal_data', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('gender', ['male', 'female', 'prefer_not_to_say']);
            $table->string('pesel');
            $table->boolean('is_gender_verified')->default(false);
            $table->boolean('is_birth_date_verified')->default(false);
            $table->boolean('is_pesel_verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_personal_data');
    }
};
