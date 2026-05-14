<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CreditCard;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(private readonly CreditCardInvoiceService $invoiceService) {}

    public function create(Request $request): Transaction
    {
        return DB::transaction(function () use ($request) {
            if ($request->input('payment_method', 'bank_account') === 'credit_card') {
                return $this->createCreditCardExpense($request);
            }

            return $this->createBankAccountTransaction($request);
        });
    }

    public function update(Transaction $transaction, Request $request): void
    {
        DB::transaction(function () use ($transaction, $request) {
            $this->reverseImpact($transaction);
            $transaction->update($this->payload($request));

            if ($transaction->fresh()->payment_method === 'credit_card') {
                $this->attachInvoice($transaction->fresh());
            }

            $this->applyImpact($transaction->fresh());
        });
    }

    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $this->reverseImpact($transaction);
            $transaction->delete();
        });
    }

    private function createBankAccountTransaction(Request $request): Transaction
    {
        $transaction = Transaction::create($this->payload($request));
        $this->applyImpact($transaction);

        return $transaction;
    }

    private function createCreditCardExpense(Request $request): Transaction
    {
        $installments = max(1, (int) $request->input('installments_total', 1));
        $amount = (float) $request->amount;
        $installmentAmount = round($amount / $installments, 2);
        $firstTransaction = null;

        for ($installment = 1; $installment <= $installments; $installment++) {
            $date = Carbon::parse($request->date)->addMonthsNoOverflow($installment - 1)->format('Y-m-d');
            $payload = $this->payload($request, [
                'amount' => $installmentAmount,
                'date' => $date,
                'account_id' => null,
                'installment_number' => $installment,
                'installments_total' => $installments,
                'parent_transaction_id' => $firstTransaction?->id,
            ]);

            $transaction = Transaction::create($payload);
            $this->attachInvoice($transaction);

            if (! $firstTransaction) {
                $firstTransaction = $transaction;
            }
        }

        return $firstTransaction;
    }

    private function payload(Request $request, array $overrides = []): array
    {
        return array_merge([
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => $request->date,
            'type' => $request->type,
            'payment_method' => $request->input('payment_method', 'bank_account'),
            'category_id' => $request->category_id,
            'user_id' => $request->user_id,
            'payment_id' => $request->payment_id,
            'account_id' => $request->account_id,
            'credit_card_id' => $request->credit_card_id,
            'invoice_id' => $request->invoice_id,
            'installment_number' => $request->installment_number,
            'installments_total' => $request->installments_total,
            'parent_transaction_id' => $request->parent_transaction_id,
            'is_invoice_payment' => $request->boolean('is_invoice_payment'),
        ], $overrides);
    }

    private function attachInvoice(Transaction $transaction): void
    {
        $creditCard = CreditCard::where('user_id', $transaction->user_id)->findOrFail($transaction->credit_card_id);
        $invoice = $this->invoiceService->findOrCreateInvoice($creditCard, $transaction->date);
        $transaction->update(['invoice_id' => $invoice->id]);
    }

    private function applyImpact(Transaction $transaction): void
    {
        if ($transaction->payment_method !== 'bank_account' || ! $transaction->account_id) {
            return;
        }

        $account = Account::where('user_id', $transaction->user_id)->lockForUpdate()->findOrFail($transaction->account_id);
        $amount = (float) $transaction->amount;

        $transaction->type === 'income'
            ? $account->increment('current_balance', $amount)
            : $account->decrement('current_balance', $amount);
    }

    private function reverseImpact(Transaction $transaction): void
    {
        if ($transaction->payment_method !== 'bank_account' || ! $transaction->account_id) {
            return;
        }

        $account = Account::where('user_id', $transaction->user_id)->lockForUpdate()->findOrFail($transaction->account_id);
        $amount = (float) $transaction->amount;

        $transaction->type === 'income'
            ? $account->decrement('current_balance', $amount)
            : $account->increment('current_balance', $amount);
    }
}
