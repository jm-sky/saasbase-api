<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->string('code', 3)->primary(); // ISO-4217 code
            $table->string('name');
            $table->string('symbol')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
