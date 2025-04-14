<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id');
            $table->uuid('user_id');
            $table->uuid('project_role_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_role_id')->references('id')->on('project_roles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_users');
    }
};
