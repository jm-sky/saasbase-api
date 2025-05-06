<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('country', 100);
            $table->string('postal_code', 20);
            $table->string('city', 100);
            $table->string('street', 150);
            $table->string('building')->nullable();
            $table->string('flat')->nullable();
            $table->string('description')->nullable();
            $table->string('type', 50); // e.g. 'residence', 'billing'
            $table->boolean('is_default')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('google_maps_url')->nullable();
            $table->uuidMorphs('addressable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
