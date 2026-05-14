<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreditCardInvoiceService
{
    public function findOrCreateInvoice(CreditCard $creditCard, string $purchaseDate): CreditCardInvoice
    {
        $date = Carbon::parse($purchaseDate);
        $referenceDate = $date->day <= $creditCard->closing_day
            ? $date->copy()
            : $date->copy()->addMonthNoOverflow();

        $referenceMonth = (int) $referenceDate->month;
        $referenceYear = (int) $referenceDate->year;
        $dueDay = min($creditCard->due_day, $referenceDate->copy()->endOfMonth()->day);
        $closingDay = min($creditCard->closing_day, $referenceDate->copy()->endOfMonth()->day);

        return CreditCardInvoice::firstOrCreate(
            [
                'credit_card_id' => $creditCard->id,
                'reference_month' => $referenceMonth,
                'reference_year' => $referenceYear,
            ],
            [
                'user_id' => $creditCard->user_id,
                'closing_date' => $referenceDate->copy()->day($closingDay)->format('Y-m-d'),
                'due_date' => $referenceDate->copy()->day($dueDay)->format('Y-m-d'),
                'status' => 'open',
            ]
        );
    }

    public function getInvoiceTotal(CreditCardInvoice $invoice): float
    {
        return (float) $invoice->transactions()
            ->where('type', 'expense')
            ->where('is_invoice_payment', false)
            ->sum('amount');
    }

    public function getUsedLimit(CreditCard $creditCard): float
    {
        return (float) Transaction::where('credit_card_id', $creditCard->id)
            ->where('type', 'expense')
            ->where('payment_method', 'credit_card')
            ->where('is_invoice_payment', false)
            ->whereHas('invoice', fn($query) => $query->whereIn('status', ['open', 'closed']))
            ->sum('amount');
    }

    public function payInvoice(CreditCardInvoice $invoice, Account $account, ?string $paymentDate = null): Transaction
    {
        return DB::transaction(function () use ($invoice, $account, $paymentDate) {
            $invoice = CreditCardInvoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();
            $account = Account::where('id', $account->id)->lockForUpdate()->firstOrFail();

            if ($invoice->status === 'paid') {
                throw new \RuntimeException('Esta fatura já está paga.');
            }

            if ($invoice->user_id !== $account->user_id) {
                throw new \RuntimeException('A conta selecionada não pertence ao usuário da fatura.');
            }

            $total = $this->getInvoiceTotal($invoice);

            if ($total <= 0) {
                throw new \RuntimeException('Não há valor em aberto para pagar nesta fatura.');
            }

            $transaction = Transaction::create([
                'user_id' => $invoice->user_id,
                'account_id' => $account->id,
                'payment_id' => null,
                'category_id' => null,
                'amount' => $total,
                'description' => sprintf('Pagamento fatura %s %02d/%d', $invoice->creditCard->name, $invoice->reference_month, $invoice->reference_year),
                'date' => $paymentDate ?: now()->format('Y-m-d'),
                'type' => 'expense',
                'payment_method' => 'bank_account',
                'is_invoice_payment' => true,
            ]);

            $account->decrement('current_balance', $total);

            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'paid_from_account_id' => $account->id,
                'payment_transaction_id' => $transaction->id,
            ]);

            return $transaction;
        });
    }
}
