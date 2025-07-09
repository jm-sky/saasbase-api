<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gtu_codes', function (Blueprint $table) {
            $table->string('code', 10)->primary();
            $table->string('name');
            $table->text('description');
            $table->decimal('amount_threshold_pln', 12, 2)->nullable();
            $table->json('applicable_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gtu_codes');
    }
};
