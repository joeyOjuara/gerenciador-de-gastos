<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('payment_method', ['bank_account', 'credit_card'])->default('bank_account')->after('type');
            $table->foreignId('credit_card_id')->nullable()->after('account_id')->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->after('credit_card_id')->constrained('credit_card_invoices')->nullOnDelete();
            $table->unsignedInteger('installment_number')->nullable()->after('invoice_id');
            $table->unsignedInteger('installments_total')->nullable()->after('installment_number');
            $table->foreignId('parent_transaction_id')->nullable()->after('installments_total')->constrained('transactions')->nullOnDelete();
            $table->boolean('is_invoice_payment')->default(false)->after('parent_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('credit_card_id');
            $table->dropConstrainedForeignId('invoice_id');
            $table->dropConstrainedForeignId('parent_transaction_id');
            $table->dropColumn(['payment_method', 'installment_number', 'installments_total', 'is_invoice_payment']);
        });
    }
};
