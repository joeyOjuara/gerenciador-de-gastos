<?php

namespace App\Repositories;

use App\Contracts\PaymentRepository;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EloquentPaymentRepository implements PaymentRepository
{
    public function all() : Collection
    {
        return Payment::where('user_id', Auth::id())->get();
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
        return Payment::where('user_id', Auth::id())->where('id', $paymentId)->firstOrFail();
    }

    public function update(Payment $payment, Request $request) : void
    {
        $payment->update($request->only(['name']));
    }

    public function orderBy(string $coluna): Collection
    {
        return Payment::where('user_id', Auth::id())->orderBy($coluna)->get();
    }
}
