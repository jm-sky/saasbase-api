<?php

use App\Domain\Financial\Enums\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->date('issue_date');
            $table->string('status')->default(InvoiceStatus::DRAFT->value);
            $table->string('ocr_status')->nullable();
            $table->string('allocation_status')->nullable();
            $table->string('approval_status')->nullable();
            $table->string('delivery_status')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('number');
            $table->foreignUlid('numbering_template_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_net', 12, 2);
            $table->decimal('total_tax', 12, 2);
            $table->decimal('total_gross', 12, 2);
            $table->string('currency', 3);
            $table->decimal('exchange_rate', 10, 6)->nullable();
            $table->json('seller');
            $table->json('buyer');
            $table->json('body');
            $table->json('payment');
            $table->json('options');
            $table->foreignUlid('created_by_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
