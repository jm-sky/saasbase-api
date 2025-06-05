<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('tenant_public_profiles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id')->unique();
            $table->string('public_name')->nullable();
            $table->text('description')->nullable();
            $table->string('website_url')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('visible')->default(false);
            $table->string('industry')->nullable();
            $table->string('location_city')->nullable();
            $table->string('location_country')->nullable();
            $table->text('address')->nullable();
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
        Schema::dropIfExists('tenant_public_profiles');
    }
};
