<?php

use App\Domain\Utils\Enums\RegistryConfirmationStatus;
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
            $table->ulid('id')->primary();
            $table->ulidMorphs('confirmable');
            $table->string('type'); // 'GUS', 'VIES', 'WhiteList'
            $table->json('payload')->nullable();
            $table->json('result')->nullable();
            $table->enum('status', RegistryConfirmationStatus::values())->default(RegistryConfirmationStatus::Pending->value);
            $table->timestamp('checked_at')->nullable();
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
