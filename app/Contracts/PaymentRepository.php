<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Models\Payment;

interface PaymentRepository
{
    public function all() : Collection;
    public function store(Request $request) : Payment;
    public function delete(Payment $payment) : void;
    public function findById(int $paymentId) : Payment;
    public function update(Payment $category, Request $request) : void;
    public function orderBy(string $coluna) : Collection;
}
