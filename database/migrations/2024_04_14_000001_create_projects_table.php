<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->uuid('status_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->uuid('owner_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('project_statuses')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
