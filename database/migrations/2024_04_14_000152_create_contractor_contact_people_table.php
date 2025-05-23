<?php

use App\Domain\Common\Enums\DatabaseColumnLength;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('contractor_contact_people', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('contractor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email', DatabaseColumnLength::EMAIL)->nullable();
            $table->string('phone', DatabaseColumnLength::PHONE)->nullable();
            $table->string('position')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('email');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractor_contact_people');
    }
};
