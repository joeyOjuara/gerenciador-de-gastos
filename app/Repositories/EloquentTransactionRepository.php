<?php

namespace App\Repositories;

use App\Contracts\TransactionRepository;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class EloquentTransactionRepository implements TransactionRepository
{
    public function all() : Collection
    {
        return Transaction::with('category')->all();
    }

    public function store(Request $request) : Transaction
    {
        return Transaction::create($request->all());
    }

    public function delete(Transaction $transaction) : void
    {
        $transaction->delete();
    }

    public function findById(int $transactionId): Transaction
    {
        return Transaction::find($transactionId);
    }

    public function update(Transaction $transaction, Request $request) : void
    {
        $transaction->update($request->all());
    }

}
