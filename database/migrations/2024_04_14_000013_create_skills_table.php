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
            $table->uuid('category_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('skill_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};
