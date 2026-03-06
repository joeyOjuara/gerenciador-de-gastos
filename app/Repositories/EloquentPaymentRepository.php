<?php

namespace App\Repositories;

use App\Contracts\PaymentRepository;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class EloquentPaymentRepository implements PaymentRepository
{
    public function all() : Collection
    {
        return Payment::all();
    }

    public function store(Request $request) : Payment
    {
        return Payment::create($request->all());
    }

    public function delete(Payment $payment) : void
    {
        $payment->delete();
    }

    public function findById(int $paymentId): Payment
    {
        return Payment::find($paymentId);
    }

    public function update(Payment $payment, Request $request) : void
    {
        $payment->update($request->all());
    }

    public function orderBy(string $coluna): Collection
    {
        return Payment::orderBy($coluna)->get();
    }
}
