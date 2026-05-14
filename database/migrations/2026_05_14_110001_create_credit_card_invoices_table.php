<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('credit_card_invoices')) {
            return;
        }

        Schema::create('credit_card_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('credit_card_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('reference_month');
            $table->unsignedSmallInteger('reference_year');
            $table->date('closing_date')->nullable();
            $table->date('due_date');
            $table->enum('status', ['open', 'closed', 'paid'])->default('open');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_from_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->timestamps();

            $table->unique(['credit_card_id', 'reference_month', 'reference_year'], 'cc_invoice_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_card_invoices');
    }
};
