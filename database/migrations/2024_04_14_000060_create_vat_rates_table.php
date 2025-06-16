<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('vat_rates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->decimal('rate', 5, 2);
            $table->string('type');
            $table->string('country_code');
            $table->boolean('active')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_rates');
    }
};
