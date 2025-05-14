<?php

use App\Domain\Common\Enums\AddressType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type')->default(AddressType::BILLING->value);
            $table->string('country');
            $table->string('postal_code')->nullable();
            $table->string('city');
            $table->string('street')->nullable();
            $table->string('building')->nullable();
            $table->string('flat')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->jsonb('meta')->nullable();

            // Polymorphic relationship fields
            $table->uuidMorphs('addressable');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
