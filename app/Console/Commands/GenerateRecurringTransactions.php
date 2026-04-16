<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Console\Command;

class GenerateRecurringTransactions extends Command
{
    protected $signature   = 'transactions:generate-recurring';
    protected $description = 'Gera novas transações a partir das recorrentes com vencimento hoje ou anterior';

    public function handle(): void
    {
        $due = Transaction::where('recurrence', '!=', 'none')
            ->whereNotNull('next_recurrence_date')
            ->where('next_recurrence_date', '<=', now()->toDateString())
            ->get();

        foreach ($due as $transaction) {
            Transaction::create([
                'type'                 => $transaction->type,
                'amount'               => $transaction->amount,
                'description'          => $transaction->description,
                'date'                 => $transaction->next_recurrence_date,
                'category_id'          => $transaction->category_id,
                'payment_id'           => $transaction->payment_id,
                'user_id'              => $transaction->user_id,
                'recurrence'           => $transaction->recurrence,
                'next_recurrence_date' => $this->nextDate($transaction->next_recurrence_date, $transaction->recurrence),
            ]);

            $transaction->update([
                'next_recurrence_date' => $this->nextDate($transaction->next_recurrence_date, $transaction->recurrence),
            ]);
        }

        $this->info("Processadas {$due->count()} transação(ões) recorrente(s).");
    }

    private function nextDate(string $from, string $recurrence): string
    {
        $date = Carbon::parse($from);
        return match ($recurrence) {
            'weekly'  => $date->addWeek()->toDateString(),
            'monthly' => $date->addMonth()->toDateString(),
            'yearly'  => $date->addYear()->toDateString(),
            default   => $date->toDateString(),
        };
    }
}
