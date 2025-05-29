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
        Schema::create('registry_confirmations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->morphs('confirmable');
            $table->string('type'); // 'GUS', 'VIES', 'WhiteList'
            $table->json('payload');
            $table->json('result');
            $table->boolean('success');
            $table->timestamp('checked_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registry_confirmations');
    }
};
