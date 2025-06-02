<?php

use App\Domain\Subscription\Enums\SubscriptionInvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('billable');
            $table->string('stripe_invoice_id')->index();
            $table->decimal('amount_due', 10, 2);
            $table->string('status')->default(SubscriptionInvoiceStatus::DRAFT->value);
            $table->string('hosted_invoice_url');
            $table->string('pdf_url');
            $table->timestamp('issued_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
    }
};
