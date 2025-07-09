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
        Schema::table('products', function (Blueprint $table) {
            $table->string('pkwiu_code')->nullable()->after('vat_rate_id');
            $table->foreign('pkwiu_code')->references('code')->on('pkwiu_classifications');
            $table->index('pkwiu_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['pkwiu_code']);
            $table->dropIndex(['pkwiu_code']);
            $table->dropColumn('pkwiu_code');
        });
    }
};
