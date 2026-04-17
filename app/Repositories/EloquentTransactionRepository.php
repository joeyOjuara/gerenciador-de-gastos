<?php

namespace App\Repositories;

use App\Contracts\TransactionRepository;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        return Transaction::where('user_id', Auth::id())->where('id', $transactionId)->firstOrFail();
    }

    public function update(Transaction $transaction, Request $request) : void
    {
        $transaction->update($request->all());
    }

}
