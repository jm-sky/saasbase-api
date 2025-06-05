<?php

use App\Domain\Common\Enums\DatabaseColumnLength;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('contractors', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('country', DatabaseColumnLength::COUNTRY)->nullable();
            $table->string('vat_id', DatabaseColumnLength::VAT_ID)->nullable();
            $table->string('tax_id', DatabaseColumnLength::TAX_ID)->nullable();
            $table->string('regon', DatabaseColumnLength::REGON)->nullable();
            $table->string('email', DatabaseColumnLength::EMAIL)->nullable();
            $table->string('phone', DatabaseColumnLength::PHONE)->nullable();
            $table->string('website', DatabaseColumnLength::WEBSITE)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_buyer')->default(true);
            $table->boolean('is_supplier')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('country');
            $table->index('vat_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
