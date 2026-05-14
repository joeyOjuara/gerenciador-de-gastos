<?php

namespace App\Repositories;

use App\Contracts\TransactionRepository;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EloquentTransactionRepository implements TransactionRepository
{
    public function __construct(private readonly TransactionService $transactionService) {}

    public function all(): Collection
    {
        return Transaction::with('category', 'payment', 'account', 'creditCard', 'invoice')->get();
    }

    public function store(Request $request): Transaction
    {
        return $this->transactionService->create($request);
    }

    public function delete(Transaction $transaction): void
    {
        $this->transactionService->delete($transaction);
    }

    public function findById(int $transactionId): Transaction
    {
        return Transaction::where('user_id', Auth::id())->where('id', $transactionId)->firstOrFail();
    }

    public function update(Transaction $transaction, Request $request): void
    {
        $this->transactionService->update($transaction, $request);
    }

    public function deleteMany(array $transactionIds): void
    {
        Transaction::where('user_id', Auth::id())
            ->whereIn('id', $transactionIds)
            ->get()
            ->each(fn(Transaction $transaction) => $this->delete($transaction));
    }
}
