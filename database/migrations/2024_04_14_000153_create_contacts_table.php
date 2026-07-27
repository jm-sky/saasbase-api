<?php

use App\Domain\Common\Enums\DatabaseColumnLength;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('position')->nullable();
            $table->string('email', DatabaseColumnLength::EMAIL)->nullable();
            $table->string('phone_number', DatabaseColumnLength::PHONE)->nullable();
            $table->jsonb('emails')->nullable();
            $table->jsonb('phone_numbers')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUlid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableUlidMorphs('contactable');
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('phone_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
