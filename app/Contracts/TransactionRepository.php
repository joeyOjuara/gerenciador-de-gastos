<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Models\Transaction;

interface TransactionRepository
{
    public function all() : Collection;
    public function store(Request $request) : Transaction;
    public function delete(Transaction $transaction) : void;
    public function findById(int $transactionId) : Transaction;
    public function update(Transaction $transaction, Request $request) : void;
}
