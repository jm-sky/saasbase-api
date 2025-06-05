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
        Schema::create('countries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('code', 2)->unique();
            $table->string('code3', 3)->unique();
            $table->string('numeric_code', 3)->unique();
            $table->string('phone_code', 10);
            $table->string('capital')->nullable();
            $table->string('currency')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->string('currency_symbol', 5)->nullable();
            $table->string('tld', 10)->nullable();
            $table->string('native')->nullable();
            $table->string('region')->nullable();
            $table->string('subregion')->nullable();
            $table->string('emoji', 10)->nullable();
            $table->string('emojiU', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
