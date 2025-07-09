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
        Schema::create('pkwiu_classifications', function (Blueprint $table) {
            $table->string('code')->primary(); // e.g., "70.22.11.0"
            $table->string('parent_code')->nullable()->index();
            $table->string('name'); // Official Polish name only
            $table->text('description')->nullable(); // Official Polish description
            $table->integer('level'); // 1-4 hierarchy levels
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['level', 'is_active']);
        });

        Schema::table('pkwiu_classifications', function (Blueprint $table) {
            $table->foreign('parent_code')->references('code')->on('pkwiu_classifications')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pkwiu_classifications');
    }
};
