<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_required_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('skill_id');
            $table->integer('required_level');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_required_skills');
    }
};
