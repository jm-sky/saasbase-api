<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('category');
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('category')->references('name')->on('skill_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};
