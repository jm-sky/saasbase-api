<?php

use App\Domain\Common\Enums\DatabaseColumnLength;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('vat_id', DatabaseColumnLength::VAT_ID)->nullable();
            $table->string('tax_id', DatabaseColumnLength::TAX_ID)->nullable();
            $table->string('regon', DatabaseColumnLength::REGON)->nullable();
            $table->string('email', DatabaseColumnLength::EMAIL)->nullable();
            $table->string('phone', DatabaseColumnLength::PHONE)->nullable();
            $table->string('website', DatabaseColumnLength::WEBSITE)->nullable();
            $table->string('country', DatabaseColumnLength::COUNTRY)->nullable();
            $table->string('description')->nullable();
            $table->foreignUlid('owner_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('country');
            $table->index('vat_id');
            $table->index('tax_id');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
