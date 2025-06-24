<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('base_currency', 3); // ISO-4217 code
            $table->string('currency', 3); // ISO-4217 code
            $table->date('date');
            $table->decimal('rate', 18, 8);
            $table->string('table'); // Table name of the rate i.e. A
            $table->string('source'); // Provider of the rate i.e. NPB
            $table->string('no')->nullable();  // Internal publishing number
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('base_currency')->references('code')->on('currencies')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('currency')->references('code')->on('currencies')->cascadeOnUpdate()->restrictOnDelete();
            $table->index(['base_currency', 'currency', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
